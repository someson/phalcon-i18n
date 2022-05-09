# Multi-lingual Support

[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://choosealicense.com/licenses/mit/)

Extending [Phalcon Framework v.3.x Translations Module](https://docs.phalcon.io/3.4/en/translate)

## Configure

default config `\Phalcon\I18n\Config\Default.php`:

```php
return [
    'defaultLang' => 'en',
    'defaultScope' => 'global',

    // loads data from chosen source (e.g. Json) by chosen loader (e.g. Files)
    // can be e.g. "Mysql" by "Database" (feel free to implement)
    'loader' => [
        'className' => \Phalcon\I18n\Loader\Files::class,
        'arguments' => ['path' => $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 'locale'],
    ],

    // reads the source and translates it into chosen type of handler (@see key "handler")
    'adapter' => [
        'className' => \Phalcon\I18n\Adapter\Json::class,
    ],

    // implements \Phalcon\Translate\AdapterInterface
    // returns an object of all translations of the specific language
    // provides functionality for placeholder replacing
    'handler' => [
        'options' => [
            'flatten' => ['shift' => 1],
        ],
    ],

    // replaces user-defined (or '%' by default) placeholders
    'interpolator' => [
        'className' => \Phalcon\I18n\Interpolator\AssocArray::class,
        'arguments' => ['{{', '}}'],
    ],

    // bool only
    'collectMissingTranslations' => true,

    // - false
    // - sprintf pattern e.g. [# %s #]
    // - \Phalcon\I18n\Interfaces\DecoratorInterface object
    'decorateMissingTranslations' => new \Phalcon\I18n\Decorator\HtmlCode,
];
```
you may want to override it with your own config (by default used in `config` container having `i18n` scope):
```php
return [
    // ...

    'i18n' => [
        'loader' => [
            'arguments' => ['path' => '/my/own/path/to/locale/'],
        ],
        'interpolator' => [
            'arguments' => ['[[', ']]'],
        ],
        'collectMissingTranslations' => false,
        'decorateMissingTranslations' => '[# %s #]',
    ],

    // ...
];
```

## Usage/Examples

### 1. Simple usage
```php
// component using a singleton pattern, so we can instantiate it before the framework itself
// or wrap it into some global function
$t = \Phalcon\I18n\Translator::instance();

// using the "de" directory, "en" by default
$t->setLang('de');

// equal to "global:a", hence "global" is a default scope
echo $t->_('a');

// placeholder "key" replaced through "value"
echo $t->_('b', ['key' => 'value']);

// nested key
echo $t->_('c.d.e', ['key' => 'value']);

// nested key from the "api" scope (filename === scope, if files used)
echo $t->_('api:c.d.e', ['key' => 'value']);
```

### 2. Advanced usage
in any bootstrap file (i.e. `index.php`) define:
```php
use \Phalcon\I18n\Translator;

if (! function_exists('__')) {
    function __(string $key, array $params = [], bool $pluralize = true): string {
        return $key ? Translator::instance()->_($key, $params, $pluralize) : '[TRANSLATION ERROR]';
    }
}
```

inside your code:
```php
$translation = __('a.b.c');
```
or in any view:
```html
<h1><?= __('a.b.c') ?></h1>
```
```twig
<h1>{{ __('a.b.c') }}</h1>
```

## Running Tests

[Codeception](https://codeception.com/) used

To run tests, run the following command:

```
$ [docker-compose exec [fpm]] ./vendor/bin/codecept run [unit] [-v[v[v]] -d] 
```
```
$ ./vendor/bin/codecept run --coverage
Codeception PHP Testing Framework v4.1.22
Powered by PHPUnit 8.5.20 by Sebastian Bergmann and contributors.

Unit Tests (29) ----------------------------------------------------------
+ AdapterTest: Json found and initialized (0.03s)
+ AdapterTest: Json may throw exceptions (0.03s)
+ ConfigTest: Must be functional with config service (0.03s)
+ ConfigTest: Must be functionable without config service (0.01s)
+ ConfigTest: Must be functionable with wrong config (0.01s)
+ DecoratorTest: No decoration (0.02s)
+ DecoratorTest: Decorate as text pattern (0.02s)
+ DecoratorTest: Decorate as html (0.02s)
+ HandlerTest: Check keys shifting | #0 (0.01s)
+ HandlerTest: Check keys shifting | #1 (0.01s)
+ HandlerTest: Check keys shifting | #2 (0.01s)
+ InterpolatorTest: Should handle default placeholders (0.01s)
+ InterpolatorTest: Should handle custom placeholders (0.01s)
+ LoaderTest: Files loader | #0 (0.01s)
+ LoaderTest: Files loader | #1 (0.01s)
+ LoaderTest: Files loader | #2 (0.01s)
+ TranslatorTest: Fallback loaded (0.02s)
+ TranslatorTest: Wrong fallback lang defined (0.02s)
+ TranslatorTest: Default instance (0.01s)
+ TranslatorTest: Change language (0.01s)
+ TranslatorTest: Change scope (0.01s)
+ TranslatorTest: Changed scope should return a new collection (0.02s)
+ TranslatorTest: Check if translation exists (0.02s)
+ TranslatorTest: Plural (0.02s)
+ TranslatorTest: Context (0.02s)
+ TranslatorTest: Missing translations (0.02s)
+ TranslatorTest: Simple translation without parameters (0.02s)
+ TranslatorTest: Simple translation with parameters (0.02s)
+ TranslatorTest: Translation with deeper level (0.02s)
--------------------------------------------------------------------------


Time: 1.33 seconds, Memory: 14.00 MB

OK (29 tests, 54 assertions)


Code Coverage Report Summary:
  Classes: 100.00% (8/8)
  Methods: 100.00% (29/29)
  Lines:   100.00% (148/148)                                              
```

For code coverage info run
```
$ ./vendor/bin/codecept run --coverage --coverage-html
```
and open `tests/_output/coverage/index.html` in your browser


## Static analyzer

`$ ./vendor/bin/phpstan analyse src --level max`

## TODOs
- caching (APCu, memcache, Redis etc.)
