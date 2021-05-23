<?php

namespace Phalcon\I18n\Adapter;

use LengthException;
use Phalcon\Config as Collection;
use Phalcon\I18n\Interfaces\AdapterInterface;
use UnexpectedValueException;

class Json extends Collection implements AdapterInterface
{
    public function __construct(string $filePath)
    {
        if (! trim($filePath)) {
            throw new UnexpectedValueException('File path not valid');
        }
        if (! $content = file_get_contents($filePath)) {
            throw new LengthException('Empty content');
        }
        if (! $data = json_decode($content, true)) {
            throw new UnexpectedValueException($this->getError());
        }
        parent::__construct($data);
    }

    public static function getFileExtensions(): array
    {
        return ['json'];
    }

    protected function getError(): string
    {
        static $messages = [
            \JSON_ERROR_NONE => 'No error has occurred',
            \JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            \JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            \JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            \JSON_ERROR_SYNTAX => 'Syntax error',
            \JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
            \JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded',
            \JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded',
            \JSON_ERROR_UNSUPPORTED_TYPE => 'A value of a type that cannot be encoded was given',
            \JSON_ERROR_INVALID_PROPERTY_NAME => 'A property name that cannot be encoded was given',
            \JSON_ERROR_UTF16 => 'Malformed UTF-16 characters, possibly incorrectly encoded',
        ];

        return $messages[json_last_error()] ?? 'Unknown json error';
    }
}
