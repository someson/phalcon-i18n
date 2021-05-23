<?php

namespace Phalcon\I18n\Interfaces;

use Phalcon\Config as Collection;

interface FileLoaderInterface
{
    /**
     * @param string $adapterClass
     * @param string $dir
     * @return Collection
     */
    public static function load(string $adapterClass, string $dir): Collection;
}
