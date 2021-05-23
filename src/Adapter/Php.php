<?php

namespace Phalcon\I18n\Adapter;

use Phalcon\Config\Adapter\Php as Collection;
use Phalcon\I18n\Interfaces\AdapterInterface;

class Php extends Collection implements AdapterInterface
{
    public static function getFileExtensions(): array
    {
        return ['php'];
    }
}
