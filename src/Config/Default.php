<?php

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
    'collectMissedTranslations' => true,

    // - false
    // - sprintf pattern e.g. [# %s #]
    // - \Phalcon\I18n\Interfaces\DecoratorInterface object
    'decorateMissedTranslations' => new \Phalcon\I18n\Decorator\HtmlCode,
];
