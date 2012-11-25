<?php

define('APP_TOPDIR', realpath(__DIR__ . '/../../php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../../vendor/php'));
define('APP_TESTDIR', realpath(__DIR__ . '/php'));

// Composer
define('APP_SRC', realpath(__DIR__ . '/../..'));
define('APP_VENDOR', realpath(__DIR__ . '/../../../vendor'));
require APP_VENDOR . '/autoload.php';

define('UNIT_TEST', true);

$app_libdir = realpath(__DIR__ . '/../../../vendor/php');

require_once($app_libdir . '/psr0.autoloader.php');

// step 2: find the autoloader, and install it
#require_once(APP_LIBDIR . '/psr0.autoloader.php');

// step 3: add the additional paths to the include path
psr0_autoloader_searchFirst(APP_LIBDIR);
psr0_autoloader_searchFirst(APP_TESTDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);