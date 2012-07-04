<?php
define('APP_TOPDIR', realpath(__DIR__ . '/../php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../vendor/php'));
define('APP_TESTDIR', realpath(__DIR__ . '/../tests/unit-tests/php'));

require_once APP_LIBDIR . '/psr0.autoloader.php';

psr0_autoloader_searchFirst(APP_LIBDIR);
psr0_autoloader_searchFirst(APP_TESTDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);

# Test server
try {
    //$config = new Zenya\Api\Config;
    $server = new Zenya\Api\Server();
    echo $server->run();

    // Zenya\Api\d( $server->getResources() );

    // Zenya\Api\d( Zenya\Api\Config::getInstance()->getRoutes() );

} catch (\Exception $e) {
    Zenya\Api\Exception::startupException($e);
}
exit;
