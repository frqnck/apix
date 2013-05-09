<?php
define('APP_TOPDIR', realpath(__DIR__ . '/../php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../vendor/php'));
// define('APP_TESTDIR', realpath(__DIR__ . '/../tests/unit-tests/php'));

require_once APP_LIBDIR . '/psr0.autoloader.php';

psr0_autoloader_searchFirst(APP_LIBDIR);
// psr0_autoloader_searchFirst(APP_TESTDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);

date_default_timezone_set('utc');
# Test server
try {
    $api = new Apix\Server('../../src/data/config_dev.php');

    $api->onRead('/test/:id',
        /**
         * Retrieve a list of articles
         * Just an example
         * @param     string  $name  Your name.
         * @return    array
         * @ api_auth  groups=clients,reselers users=franck
         * @api_cache ttl=1day tags=author
         */
         function($filters=null) {
            // some logic
            return array('$results');
        });

    echo $api->run();

} catch (\Exception $e) {
    Apix\Exception::startupException($e);
}

// flush();
exit;
