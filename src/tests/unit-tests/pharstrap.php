<?php
// phpunit --bootstrap src/tests/unit-tests/pharstrap.php

define('UNIT_TEST', true);

define('APP_TESTDIR', realpath(__DIR__ . '/php'));

try {
     // Phar::mapPhar('apix.phar');
    spl_autoload_register(function($name){
        $phar = 'phar://' . realpath(__DIR__ . '/../../../dist/apix.phar');

        $file = '/' . str_replace('\\', DIRECTORY_SEPARATOR, $name).'.php';
        // $path = 'phar://apix.phar/src/php' . $file;
        $path = $phar . '/src/php' . $file;

        if (file_exists($path)) require $path;
    });
} catch (Exception $e) {
    die('Error: cannot initialize - ' . $e->getMessage());
}

$app_libdir = realpath(__DIR__ . '/../../../vendor/php');
require_once $app_libdir . '/psr0.autoloader.php';
psr0_autoloader_searchFirst(APP_TESTDIR);
