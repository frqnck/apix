<?php
define('APP_TOPDIR', realpath(__DIR__ . '/../php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../vendor/php'));
define('APP_TESTDIR', realpath(__DIR__ . '/../tests/unit-tests/php'));

#ini_set('zlib.output_compression', 1);

require_once APP_LIBDIR . '/psr0.autoloader.php';

psr0_autoloader_searchFirst(APP_LIBDIR);
psr0_autoloader_searchFirst(APP_TESTDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);

# Test server
try {
    #$server = new Apix\Server(require "../../src/data/config.dist.php");

    $server = new Apix\Server('../../src/data/config.dist.php');
    echo $server->run();

    // Apix\d( $server->getResources() );
    // Apix\d( Apix\Config::getInstance()->getRoutes() );

} catch (\Exception $e) {
    Apix\Exception::startupException($e);
}
exit;