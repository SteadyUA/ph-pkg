<?php

namespace SteadyUa\PhPkg;

use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Script\Event;

class Installer
{
    private $data;
    private $type;

    protected function __construct(array $data, string $type)
    {
        $data['__EMPTY__'] = '';
        $this->data = $data;
        $this->type = $type;
    }

    public function make(array $composerData): array
    {
        $instructionsFile = __DIR__ . '/' . $this->type . '/install.json';
        $instructions = (new JsonFile($instructionsFile))->read();
        if (isset($instructions['extends'])) {
            $composerData = (new self($this->data, $instructions['extends']))->make($composerData);
        }

        foreach ($instructions['mkdir'] ?? [] as $dirPath) {
            $dirPath = strtr($dirPath, $this->data);
            mkdir(__DIR__ . '/' . $dirPath);
        }

        foreach ($instructions['copy'] ?? [] as $sourceFile => $destinationDir) {
            $destinationDir = strtr($destinationDir, $this->data);
            $contents = file_get_contents(__DIR__ . "/{$this->type}/" . $sourceFile);
            $contents = strtr($contents, $this->data);
            file_put_contents(
                __DIR__ . "/" . $destinationDir . '/' . strtr($sourceFile, $this->data),
                $contents
            );
        }

        foreach ($instructions['composer.json'] ?? [] as $section => $sectionValues) {
            $composerData[$section] = $this->trRecursive($sectionValues);
        }

        return $composerData;
    }

    private function trRecursive($source)
    {
        if (is_string($source)) {
            return strtr($source, $this->data);
        } elseif (is_array($source)) {
            $res = [];
            foreach ($source as $key => $value) {
                $res[strtr($key, $this->data)] = $this->trRecursive($value);
            }
            return $res;
        }

        return $source;
    }

    private static function build(IOInterface $io): self
    {
        // installation type
        $default = 'minimal';
        $types = ['minimal', 'service'];
        $res = $io->select(
            "<question> Type </question>\n default (<comment>{$default}</comment>): ",
            $types,
            0
        );
        $type = $types[$res] ?? $types[$default];
        echo "\n";

        // package name
        $default = basename(realpath(getcwd() . '/.'));
        $res = $io->ask(
            "<question> Package name </question>\n phoenix/(<comment>{$default}</comment>): ",
            $default
        );
        $data['__PKG_NAME__'] = "phoenix/{$res}";
        echo "\n";

        // package namespace
        $default = str_replace(' ', '', ucwords(str_replace('-', ' ', $res)));
        $res = $io->ask(
            "<question> Namespace </question>\n Phoenix/(<comment>{$default}</comment>): ",
            $default
        );
        $data['__PKG_NS__'] = "Phoenix\\{$res}";
        echo "\n";

        // service installation questions
        if ($type == 'service') {
            $default = 'Foo';
            $res = $io->ask(
                "<question> Service name </question>\n (<comment>$default</comment>): ",
                $default
            );
            $data['__SERVICE_NAME__'] = str_replace(' ', '', ucwords(str_replace('-', ' ', $res)));
            echo "\n";
        }

        return new self($data, $type);
    }

    private static function rmDir($dirPath)
    {
        $files = glob($dirPath . '/*');
        foreach ($files as $file) {
            if (!is_dir($file)) {
                unlink($file);
            } else {
                self::rmDir($file);
            }
        }
        rmdir($dirPath);
    }

    public static function postInstall(Event $event)
    {
        $json = new JsonFile(Factory::getComposerFile());
        $resultJson = self::build($event->getIO())->make($json->read());
        unset(
            $resultJson['description'],
            $resultJson['scripts'],
        );
        $resultJson["license"] = "proprietary";
        $json->write($resultJson);
        self::rmDir(__DIR__ . '/minimal');
        self::rmDir(__DIR__ . '/service');
        self::rmDir(__DIR__ . '/vendor');
        unlink(__DIR__ . '/readme.md');
        unlink(__DIR__ . '/composer.lock');
        unlink(__FILE__);
    }
}
