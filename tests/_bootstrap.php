<?php

error_reporting(-1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('utf-8');
}

clearstatcache();
