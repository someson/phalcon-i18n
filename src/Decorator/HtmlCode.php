<?php

namespace Phalcon\I18n\Decorator;

use Phalcon\I18n\Interfaces\DecoratorInterface;

class HtmlCode implements DecoratorInterface
{
    public function decorate(string $key): string
    {
        return sprintf('<code>%s</code>', $key);
    }
}
