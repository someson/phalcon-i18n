<?php

namespace Phalcon\I18n\Interfaces;

interface HandlerInterface
{
    public function getByScope(string $name): array;
}
