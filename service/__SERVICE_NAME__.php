<?php

namespace __PKG_NS__\Service;

use __PKG_NS__\__SERVICE_NAME__Service;

class __SERVICE_NAME__ implements __SERVICE_NAME__Service
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }
}
