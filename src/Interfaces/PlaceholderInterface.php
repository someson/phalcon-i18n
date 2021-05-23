<?php

namespace Phalcon\I18n\Interfaces;

interface PlaceholderInterface
{
    public function defineBrackets(?string $left = null, ?string $right = null): void;
}
