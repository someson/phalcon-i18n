<?php

namespace Phalcon\I18n\Handler;

use Phalcon\I18n\Interfaces\HandlerInterface;
use Phalcon\Translate\Adapter\NativeArray as PhalconNativeArray;

class NativeArray extends PhalconNativeArray implements HandlerInterface
{
    /** @var int */
    protected $_shiftLevel;

    /** @var array */
    protected $_shiftKeys;

    /** @var string */
    protected $_flatSeparator;

    /**
     * @param array $options
     *
     * shift => 0, // ['a.b.c.d' => 'translation value']
     * shift => 1, // ['a' => ['b.c.d' => 'translation value']]
     * shift => 2, // ['a' => ['b' => ['c.d' => 'translation value']]]
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->_shiftKeys = [];
        if (isset($options['flatten'])) {
            $flatOption = $options['flatten'];
            if (is_array($flatOption)) {
                $this->_shiftLevel = $flatOption['shift'] ?? 0;
                $this->_flatSeparator = $flatOption['separator'] ?? '.';
            }
            $this->_translate = $this->_flatten($this->_translate);
        }
    }

    /**
     * @param string $index
     * @return bool
     */
    public function exists($index): bool
    {
        $found = $this->_translate;
        foreach ($this->_shiftKeys as $key) {
            if (! isset($found[$key])) {
                return false;
            }
            $found = $found[$key];
        }
        return isset($found[$index]);
    }

    /**
     * list of keys ['a'] before actual main key ['b.c.d']
     * must be set if self::_shiftLevel > 0 (multidimensional array)
     * @param string[] $keys
     */
    public function shiftKeys(...$keys): void
    {
        $this->_shiftKeys = $keys;
    }

    /**
     * @param string $index
     * @param null|array $placeholders
     * @return string
     */
    public function query($index, $placeholders = null): string
    {
        $found = $this->_translate;
        foreach ($this->_shiftKeys as $key) {
            $found = $found[$key];
        }
        return $this->replacePlaceholders($found[$index], $placeholders);
    }

    /**
     * @param string $name
     * @return array
     */
    public function getByScope(string $name): array
    {
        return $this->_translate[$name] ?? [];
    }

    /**
     * @param array $data
     * @param string $prefix
     * @param int $shiftLevel
     * @return array
     */
    protected function _flatten(array $data, string $prefix = '', int $shiftLevel = 0): array
    {
        $output = [];
        foreach ($data as $key => $value) {
            $keyChain = $this->_shiftLevel > $shiftLevel ?
                $key : ltrim($prefix . $this->_flatSeparator . $key, $this->_flatSeparator);

            if (is_array($value) || is_object($value)) {
                if ($this->_shiftLevel > $shiftLevel) {
                    $output[$keyChain] = $this->_flatten($value, '', $shiftLevel + 1);
                } else {
                    $output += $this->_flatten($value, $keyChain, $shiftLevel + 1);
                }
            } else {
                $output[$keyChain] = $value;
            }
        }
        return $output;
     }
}
