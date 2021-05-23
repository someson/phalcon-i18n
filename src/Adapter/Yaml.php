<?php

namespace Phalcon\I18n\Adapter;

use Phalcon\Config\Adapter\Yaml as Collection;
use Phalcon\I18n\Interfaces\AdapterInterface;

class Yaml extends Collection implements AdapterInterface
{
    public static function getFileExtensions(): array
    {
        return ['yml', 'yaml'];
    }
}
