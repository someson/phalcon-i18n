<?php

namespace Phalcon\I18n\Handler;

use Phalcon\I18n\Interfaces\HandlerInterface;
use Phalcon\Translate\Adapter\AbstractAdapter;
use Phalcon\Translate\Exception;
use Phalcon\Translate\Interpolator\InterpolatorInterface;
use Phalcon\Translate\InterpolatorFactory;

class NativeArray extends AbstractAdapter implements HandlerInterface
{
    /** @var array<string, mixed> */
    protected array $_translate;
    protected InterpolatorInterface $_interpolator;

    /** @var array<int, string> */
    protected array $_shiftKeys;
    protected int $_shiftLevel;
    protected string $_flatSeparator;

    /**
     * @param InterpolatorInterface $interpolator
     * @param array<string, mixed> $options
     * @throws Exception
     * shift => 0, // ['a.b.c.d' => 'translation value']
     * shift => 1, // ['a' => ['b.c.d' => 'translation value']]
     * shift => 2, // ['a' => ['b' => ['c.d' => 'translation value']]]
     */
    public function __construct(InterpolatorInterface $interpolator, array $options)
    {
        $this->_interpolator = $interpolator;

        // as a fallback in order to use methods of AbstractAdapter. Components of factories almost unextenadable :(
        $interpolatorFactory = new InterpolatorFactory(['defaultInterpolator' => get_class($this->_interpolator)]);
        parent::__construct($interpolatorFactory, $options);

        if (! isset($options['content'])) {
            throw new Exception('Translation content was not provided');
        }
        $this->_translate = $options['content'];
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
    public function has(string $index): bool
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
     * @param string $keys
     */
    public function shiftKeys(...$keys): void
    {
        $this->_shiftKeys = $keys;
    }

    /**
     * @param string $index
     * @param array<string, mixed> $placeholders
     * @return string
     */
    public function query(string $index, array $placeholders = []): string
    {
        $found = $this->_translate;
        foreach ($this->_shiftKeys as $key) {
            $found = $found[$key];
        }
        return $this->replacePlaceholders($found[$index], $placeholders);
    }

    /**
     * @param string $name
     * @return array<string, mixed>
     */
    public function getByScope(string $name): array
    {
        return $this->_translate[$name] ?? [];
    }

    /**
     * @param array<string, mixed> $data
     * @param string $prefix
     * @param int $shiftLevel
     * @return array<string, mixed>
     */
    protected function _flatten(array $data, string $prefix = '', int $shiftLevel = 0): array
    {
        $output = [];
        foreach ($data as $key => $value) {
            $keyChain = $this->_shiftLevel > $shiftLevel ?
                $key : ltrim($prefix . $this->_flatSeparator . $key, $this->_flatSeparator);

            if (is_array($value)) {
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

    /**
     * @param string $translation
     * @param array<string, mixed> $placeholders
     * @return string
     */
    protected function replacePlaceholders(string $translation, array $placeholders = []): string
    {
        return $this->_interpolator->replacePlaceholders($translation, $placeholders);
    }
}
