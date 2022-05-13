<?php

namespace Phalcon\I18n\Interfaces;

interface HandlerInterface
{
    /**
     * @param string $name
     * @return array<string, mixed>
     */
    public function getByScope(string $name): array;
}
