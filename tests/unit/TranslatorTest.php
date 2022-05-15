<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Translator;

class TranslatorTest extends Unit
{
    protected Translator $translator;
    protected \UnitTester $tester;

    protected function _before(): void
    {
        parent::_before();
        $this->tester->addServiceToContainer('config', new \Phalcon\Config\Config([
            'i18n' => [
                'loader' => ['arguments' => ['path' => FIXTURES . DIRECTORY_SEPARATOR . 'locale']],
            ],
        ]), true);
        $this->translator = Translator::instance();
        $this->translator->initialize();
        $this->translator->setLang('de');
    }

    public function testFallbackLoaded(): void
    {
        $this->translator->setLang('fr')->setScope('test');

        self::assertTrue($this->translator->exists('a1'));

        $scope = $this->translator->getScope('test');
        self::assertIsArray($scope);
        self::assertArrayHasKey('a1', $scope);

        $reflection = new \ReflectionClass($this->translator);
        $langProperty = $reflection->getProperty('_lang');
        $langProperty->setAccessible(true);
        self::assertSame($langProperty->getValue($this->translator), 'en');
    }

    public function testWrongFallbackLangDefined(): void
    {
        $this->translator->initialize(['defaultLang' => 'zz']);
        $this->translator->setLang('fr')->setScope('test');

        $this->tester->expectThrowable(\Phalcon\Translate\Exception::class, function() {
            $this->translator->exists('a1');
        });
    }

    public function testDefaultInstance(): void
    {
        self::assertSame($this->translator->getScopeName(), 'global');

        $reflection = new \ReflectionClass($this->translator);
        $langProperty = $reflection->getProperty('_lang');
        $langProperty->setAccessible(true);
        self::assertSame($langProperty->getValue($this->translator), 'de');
    }

    public function testChangeLanguage(): void
    {
        $this->translator->setLang('en');

        $reflection = new \ReflectionClass($this->translator);
        $langProperty = $reflection->getProperty('_lang');
        $langProperty->setAccessible(true);
        self::assertSame($langProperty->getValue($this->translator), 'en');
    }

    public function testChangeScope(): void
    {
        $this->translator->setScope('test');
        self::assertSame($this->translator->getScopeName(), 'test');
    }

    public function testChangedScopeShouldReturnANewCollection(): void
    {
        $this->translator->setScope('test');
        self::assertTrue($this->translator->exists('a1'));

        $scope = $this->translator->getScope('test');
        self::assertIsArray($scope);
        self::assertArrayHasKey('a1', $scope);
        self::assertArrayNotHasKey('a2', $scope);
        self::assertEmpty($this->translator->getScope('not-existed-scope-name'));
    }

    public function testCheckIfTranslationExists(): void
    {
        $this->translator->setScope('test');
        self::assertTrue($this->translator->exists('a1'));
        self::assertSame($this->translator->_('a1'), 'Test Übersetzung');
        self::assertFalse($this->translator->exists('not-existing-key'));
        self::assertFalse($this->translator->exists('wrong-scope:a1'));
        self::assertTrue($this->translator->exists('global:a4.b2.c1'));
        self::assertFalse($this->translator->exists('global:a99'));

        $config = $this->translator->getConfig();
        $config->offsetSet('decorateMissingTranslations', false);
        self::assertSame($this->translator->_('a99'), 'a99');
        self::assertSame($this->translator->_('global:a99'), 'a99');
    }

    public function testPlural(): void
    {
        $this->translator->setScope('global');

        $t = $this->translator->_('a5');
        self::assertSame($t, 'Beispiel (Singular)');

        $t = $this->translator->_('a5', ['count' => 5]);
        self::assertSame($t, '5 Beispiele (Plural)');
    }

    public function testContext(): void
    {
        $t = $this->translator->_('a6', ['context' => 'context']);
        self::assertSame($t, 'mit context');
    }

    public function testMissingTranslations(): void
    {
        $key = 'NOT_EXISTING_KEY';
        $t = $this->translator->_($key);
        self::assertArrayHasKey($key, $this->translator->getMissingTranslations());
    }

    public function testSimpleTranslationWithoutParameters(): void
    {
        $t = $this->translator->_('a1');
        self::assertSame($t, 'Test Übersetzung 1');
    }

    public function testSimpleTranslationWithParameters(): void
    {
        $t = $this->translator->_('a2', ['p1' => 'TEST']);
        self::assertSame($t, 'Test Übersetzung mit einem Parameter: TEST');

        $t = $this->translator->_('a3', ['p1' => 'TEST1', 'p2' => 'TEST2']);
        self::assertSame($t, 'Test Übersetzung mit 2 Parametern: TEST1 und TEST2');
    }

    public function testTranslationWithDeeperLevel(): void
    {
        $t = $this->translator->_('a4.b1');
        self::assertSame($t, 'Test Übersetzung level 2');

        $t = $this->translator->_('a4.b2.c1');
        self::assertSame($t, 'Test Übersetzung level 3');
    }
}
