<?php

namespace Phalcon\I18n\Interfaces;

interface AdapterInterface
{
    /**
     * @return string[]
     */
    public static function getFileExtensions(): array;
}
