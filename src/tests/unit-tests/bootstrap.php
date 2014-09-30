<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix;

define('DEBUG', true);

define('UNIT_TEST', true);
define('APP_TOPDIR', realpath(__DIR__ . '/../../php'));
define('APP_TESTDIR', realpath(__DIR__ . '/php'));
define('APP_VENDOR', realpath(__DIR__ . '/../../../vendor'));

// Composer
// define('APP_SRC', realpath(__DIR__ . '/../..'));
require APP_VENDOR . '/autoload.php';

// PEAR
// $pear_libdir = realpath(__DIR__ . '/../../../vendor/php');
// require_once($pear_libdir . '/psr0.autoloader.php');
// define('APP_LIBDIR', realpath(__DIR__ . '/../../../vendor/php'));
// require APP_LIBDIR . '/psr0.autoloader.php';
// psr0_autoloader_searchFirst(APP_LIBDIR);
// psr0_autoloader_searchFirst(APP_TESTDIR);
// psr0_autoloader_searchFirst(APP_TOPDIR);

// @TODO: this won't work with PEAR
require APP_VENDOR . '/apix/autoloader/src/php/Apix/Autoloader.php';
Autoloader::init(
    array(APP_TOPDIR, APP_TESTDIR, APP_VENDOR)
);
