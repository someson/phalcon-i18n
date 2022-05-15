<?php

namespace Phalcon\I18n\Interfaces;

interface AdapterInterface
{
    /**
     * @return array<int, string>
     */
    public static function getFileExtensions(): array;
}
