<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Translator;

class ConfigTest extends Unit
{
    protected \UnitTester $tester;

    public function testMustBeFunctionalWithConfigService(): void
    {
        $path = FIXTURES . DIRECTORY_SEPARATOR . 'locale';
        $this->tester->addServiceToContainer('config', new \Phalcon\Config\Config([
            'i18n' => [
                'loader' => ['arguments' => ['path' => $path]],
            ],
        ]), true);

        $translator = Translator::instance();
        $translator->setLang('de');
        self::assertSame($path, $translator->getConfig()->path('loader.arguments.path'));

        $t = $translator->_('a1');
        self::assertSame($t, 'Test Übersetzung 1');
    }

    public function testMustBeFunctionableWithoutConfigService(): void
    {
        $path = FIXTURES . DIRECTORY_SEPARATOR . 'locale';
        $translator = Translator::instance();
        $translator->initialize([
            'loader' => ['arguments' => ['path' => $path]],
        ]);
        $translator->setLang('de');
        self::assertSame($path, $translator->getConfig()->path('loader.arguments.path'));

        $t = $translator->_('a1');
        self::assertSame($t, 'Test Übersetzung 1');
    }

    public function testMustBeFunctionableWithWrongConfig(): void
    {
        $this->tester->addServiceToContainer('config', new \Phalcon\Config\Config());

        $translator = Translator::instance();
        $translator->initialize();
        $translator->setLang('de');

        self::assertSame(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'locale',
            $translator->getConfig()->path('loader.arguments.path')
        );
    }
}
