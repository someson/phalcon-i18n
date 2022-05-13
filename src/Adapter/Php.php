<?php

namespace Phalcon\I18n\Adapter;

use Phalcon\Config\Adapter\Php as PhpBase;
use Phalcon\I18n\Interfaces\AdapterInterface;

class Php extends PhpBase implements AdapterInterface
{
    public static function getFileExtensions(): array
    {
        return ['php'];
    }
}
