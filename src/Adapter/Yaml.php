<?php

namespace Phalcon\I18n\Adapter;

use Phalcon\Config\Adapter\Yaml as YamlBase;
use Phalcon\I18n\Interfaces\AdapterInterface;

class Yaml extends YamlBase implements AdapterInterface
{
    public static function getFileExtensions(): array
    {
        return ['yml', 'yaml'];
    }
}
