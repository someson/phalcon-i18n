<?php

namespace Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Translator;

class DecoratorTest extends Unit
{
    protected Translator $translator;
    protected \UnitTester $tester;

    protected function _before(): void
    {
        parent::_before();
        $this->tester->addServiceToContainer('config', new \Phalcon\Config\Config([
            'i18n' => [
                'loader' => ['arguments' => ['path' => FIXTURES . DIRECTORY_SEPARATOR .'locale']],
            ],
        ]), true);
        $this->translator = Translator::instance();
        $this->translator->initialize();
    }

    public function testNoDecoration(): void
    {
        $this->translator->getConfig()->offsetSet('decorateMissingTranslations', false);
        $this->translator->setLang('de');

        $key = 'NOT_EXISTING_KEY';
        $t = $this->translator->_($key);
        self::assertSame($t, $key);
    }

    public function testDecorateAsTextPattern(): void
    {
        $pattern = '[# %s #]';
        $this->translator->getConfig()->offsetSet('decorateMissingTranslations', $pattern);
        $this->translator->setLang('de');

        $key = 'NOT_EXISTING_KEY';
        $t = $this->translator->_($key);
        self::assertSame($t, sprintf($pattern, $key));
    }

    public function testDecorateAsHtml(): void
    {
        $this->translator->getConfig()->offsetSet('decorateMissingTranslations', new \Phalcon\I18n\Decorator\HtmlCode);
        $this->translator->setLang('de');

        $key = 'NOT_EXISTING_KEY';
        $t = $this->translator->_($key);
        self::assertSame($t, sprintf('<code>%s</code>', $key));
    }
}
