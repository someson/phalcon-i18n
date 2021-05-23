<?php

namespace Phalcon\I18n\Interpolator;

use Phalcon\I18n\Interfaces\PlaceholderInterface;
use Phalcon\Translate\InterpolatorInterface;

class AssocArray implements InterpolatorInterface, PlaceholderInterface
{
    /** @var string */
    protected $_bracketLeft;

    /** @var string */
    protected $_bracketRight;

    public function __construct(?string $left = null, ?string $right = null)
    {
        $this->defineBrackets($left, $right);
    }

    /**
     * {@inheritDoc}
     */
    public function replacePlaceholders($translation, $placeholders = null): string
    {
        if (is_array($placeholders)) {
            foreach ($placeholders as $key => $replace) {
                $search = $this->_bracketLeft . $key . $this->_bracketRight;
                $translation = str_replace($search, $replace, $translation);
            }
        }
        return $translation;
    }

    public function defineBrackets(?string $left = null, ?string $right = null): void
    {
        $this->_bracketLeft = $left ?? '%';
        $this->_bracketRight = $right ?? '%';
    }
}
