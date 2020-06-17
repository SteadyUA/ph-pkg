<?php

namespace __PKG_NS__\Service;

use __PKG_NS__\__SERVICE_NAME__Service;
use __PKG_NS__\Service\Entity\__SERVICE_NAME__Storage;

class __SERVICE_NAME__ implements __SERVICE_NAME__Service
{
    /**
     * @var __SERVICE_NAME__Storage
     */
    private $storage;

    public function __construct(__SERVICE_NAME__Storage $storage)
    {
        $this->storage = $storage;
    }
}
