<?php

namespace Phalcon\I18n\Interpolator;

use Phalcon\I18n\Interfaces\PlaceholderInterface;
use Phalcon\Translate\Interpolator\InterpolatorInterface;

class AssocArray implements InterpolatorInterface, PlaceholderInterface
{
    protected string $_bracketLeft;
    protected string $_bracketRight;

    public function __construct(?string $left = null, ?string $right = null)
    {
        $this->defineBrackets($left, $right);
    }

    /**
     * Replaces placeholders by the values passed
     * @param string $translation
     * @param array<string, string|array> $placeholders
     * @return string
     */
    public function replacePlaceholders(string $translation, array $placeholders = []): string
    {
        foreach ($placeholders as $key => $replace) {
            $search = $this->_bracketLeft . $key . $this->_bracketRight;
            $translation = str_replace($search, $replace, $translation);
        }
        return $translation;
    }

    public function defineBrackets(?string $left = null, ?string $right = null): void
    {
        $this->_bracketLeft = $left ?? '%';
        $this->_bracketRight = $right ?? '%';
    }
}
