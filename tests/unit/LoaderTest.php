<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Adapter\{ Json, Php, Yaml };
use Phalcon\I18n\Loader\Files;

class LoaderTest extends Unit
{
    public function adapterList(): array
    {
        $adapters = [
            [Json::class], // should be loaded by default
            [Php::class],
            // ...
        ];
        if (extension_loaded('yaml')) {
            $adapters[] = [Yaml::class];
        }
        return $adapters;
    }

    /**
     * @dataProvider adapterList
     * @param string $adapter
     */
    public function testFilesLoader(string $adapter): void
    {
        $loaded = Files::load($adapter, FIXTURES . '/locale/de');
        $content = $loaded->toArray();
        self::assertNotEmpty($content);
        self::assertArrayHasKey('global', $content);
    }
}
