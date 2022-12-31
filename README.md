# Multi-lingual Support

[![MIT License](https://img.shields.io/apm/l/atomic-design-ui.svg?)](https://choosealicense.com/licenses/mit/)
[![CircleCI](https://circleci.com/gh/someson/phalcon-i18n/tree/5.0.svg?style=shield)](https://circleci.com/gh/someson/phalcon-i18n/tree/circleci-project-setup)
[![codecov](https://codecov.io/gh/someson/phalcon-i18n/branch/5.0/graph/badge.svg?token=AW5T4WU56Q)](https://codecov.io/gh/someson/phalcon-i18n)
![Packagist Version (including pre-releases)](https://img.shields.io/packagist/v/someson/phalcon-i18n)
[![Made in Ukraine](https://img.shields.io/badge/made_in-ukraine-ffd700.svg?labelColor=0057b7)](https://supportukrainenow.org/)
[![Russian Warship Go Fuck Yourself](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/badges/RussianWarship.svg)](https://stand-with-ukraine.pp.ua)

Extending [Phalcon Framework v5 Translations Module](https://docs.phalcon.io/5.0/en/translate)

## Install

```bash
$ composer require someson/phalcon-i18n
```

## Example

e.g. `login.json` File:
```json
{
    "form": {
        "label": {
            "identity": "Benutzername",
            "password": "Passwort",
            "rememberMe": "Ich möchte angemeldet bleiben"
        },
        "placeholder": {
            "identity": "Bitte geben Sie ihren Benutzernamen ein",
            "password": "Bitte geben Sie ihr Passwort ein"
        },
        "button": "Anmelden"
    },
    "title": {
        "h1": "Main Title",
        "h2": "some subtitle"
    }
}
```
translating path like `login:form.label.identity` returns `Benutzername`

which start with `login:` means file name and the rest of it is a pure json path.

## Usage

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

## Running Tests

[Codeception](https://codeception.com/) used

```
$ docker-compose exec i18n ./vendor/bin/codecept build
```

To run tests, run the following command:
```
$ docker-compose exec i18n ./vendor/bin/codecept run [-vv]
```

For code coverage info run
```
$ docker-compose exec i18n ./vendor/bin/codecept run --coverage --coverage-html
```
and open `tests/_output/coverage/index.html` in your browser

### Static analyzer

`$ docker-compose exec i18n ./vendor/bin/phpstan analyse src --level max`
