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
try {
     // Phar::mapPhar('apix.phar');
    spl_autoload_register(function ($name) {
        $phar = 'phar://' . realpath(__DIR__ . '/../../../dist/apix.phar');

        $file = '/' . str_replace('\\', DIRECTORY_SEPARATOR, $name).'.php';
        // $path = 'phar://apix.phar/src/php' . $file;
        $path = $phar . '/src/php' . $file;

        if (file_exists($path)) require $path;
    });
} catch (Exception $e) {
    die('Error: cannot initialize - ' . $e->getMessage());
}

include 'bootstrap.php';