<?php

namespace Phalcon\I18n\Interfaces;

use Phalcon\Config\ConfigInterface;

interface FileLoaderInterface
{
    /**
     * @param string $adapterClass FQCN
     * @param string $dir
     * @return ConfigInterface
     */
    public static function load(string $adapterClass, string $dir): ConfigInterface;
}
