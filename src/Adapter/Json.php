<?php

namespace Phalcon\I18n\Adapter;

use JsonException;
use LengthException;
use Phalcon\Support\Collection;
use Phalcon\I18n\Interfaces\AdapterInterface;
use UnexpectedValueException;

class Json extends Collection implements AdapterInterface
{
    /**
     * @param string $filePath
     * @throws JsonException
     */
    public function __construct(string $filePath)
    {
        if (! trim($filePath)) {
            throw new UnexpectedValueException('File path not valid');
        }
        if (! $content = file_get_contents($filePath)) {
            throw new LengthException('Empty content');
        }
        $data = json_decode($content, true, 100, JSON_THROW_ON_ERROR);
        parent::__construct((array) $data, false);
    }

    public static function getFileExtensions(): array
    {
        return ['json'];
    }
}
