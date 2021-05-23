<?php

namespace Phalcon\Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Adapter\Json;

class AdapterTest extends Unit
{
    /** @var \UnitTester */
    protected $tester;

    public function testJsonFoundAndInitialized(): void
    {
        $filePath = sprintf('%s/locale/de/global.json', FIXTURES);
        self::assertInstanceOf(Json::class, new Json($filePath));
    }

    public function testJsonMayThrowExceptions(): void
    {
        $this->tester->comment('No file path:');
        $this->tester->expectThrowable(new \UnexpectedValueException('File path not valid'), function() {
            new Json('');
        });

        $this->tester->comment('Empty file:');
        $this->tester->expectThrowable(new \LengthException('Empty content'), function() {
            new Json(FIXTURES . '/corrupted/empty.json');
        });

        $this->tester->comment('Json syntax error:');
        $this->tester->expectThrowable(new \UnexpectedValueException('Syntax error'), function() {
            new Json(FIXTURES . '/corrupted/syntax-error.json');
        });
    }
}
