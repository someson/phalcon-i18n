<?php

namespace Phalcon\Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\I18n\Translator;

class DecoratorTest extends Unit
{
    /** @var Translator */
    protected $translator;

    /** @var \UnitTester */
    protected $tester;

    protected function _before(): void
    {
        parent::_before();
        $this->tester->addServiceToContainer('config', new \Phalcon\Config([
            'i18n' => [
                'loader' => ['arguments' => ['path' => FIXTURES . DIRECTORY_SEPARATOR .'locale']],
            ],
        ]), true);
        $this->translator = Translator::instance();
        $this->translator->initialize();
    }

    public function testNoDecoration(): void
    {
        $this->translator->getConfig()->offsetSet('decorateMissedTranslations', false);
        $this->translator->setLang('de');

        $key = 'NOT_EXISTING_KEY';
        $t = $this->translator->_($key);
        self::assertSame($t, $key);
    }

    public function testDecorateAsTextPattern(): void
    {
        $pattern = '[# %s #]';
        $this->translator->getConfig()->offsetSet('decorateMissedTranslations', $pattern);
        $this->translator->setLang('de');

        $key = 'NOT_EXISTING_KEY';
        $t = $this->translator->_($key);
        self::assertSame($t, sprintf($pattern, $key));
    }

    public function testDecorateAsHtml(): void
    {
        $this->translator->getConfig()->offsetSet('decorateMissedTranslations', new \Phalcon\I18n\Decorator\HtmlCode);
        $this->translator->setLang('de');

        $key = 'NOT_EXISTING_KEY';
        $t = $this->translator->_($key);
        self::assertSame($t, sprintf('<code>%s</code>', $key));
    }
}
