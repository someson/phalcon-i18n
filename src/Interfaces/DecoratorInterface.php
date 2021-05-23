<?php

namespace Phalcon\I18n\Interfaces;

interface DecoratorInterface
{
    public function decorate(string $key): string;
}
