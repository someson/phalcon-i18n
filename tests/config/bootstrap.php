<?php

defined('BASE_DIR') || define('BASE_DIR', dirname(__DIR__, 2));
defined('FIXTURES') || define('FIXTURES', BASE_DIR . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . '_fixtures');
$_SERVER['DOCUMENT_ROOT'] = BASE_DIR;

require_once BASE_DIR . '/vendor/autoload.php';
return new \Phalcon\Mvc\Application(new \Phalcon\Di\FactoryDefault());
