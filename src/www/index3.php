<?php
#echo $_SERVER["SCRIPT_FILENAME"];
#exit;
/*
define('APP_TOPDIR', realpath(__DIR__ . '/../php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../vendor/php'));
define('APP_TESTDIR', realpath(__DIR__ . '/../tests/unit-tests/php'));

require_once APP_LIBDIR . '/psr0.autoloader.php';

psr0_autoloader_searchFirst(APP_LIBDIR);
psr0_autoloader_searchFirst(APP_TESTDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);
*/

require_once __DIR__ . '../../../dist/zenya-api-server.phar';

# Test server
try {
    #$api = new Apix\Server;

    // Test server

    $api = new Apix\Server(require "../../src/data/config.dist.php");

    $api->onRead('/version/:software',
        /**
         * Returns the last version of the :software
         *
         * Blahh blahh...
         *
         * @param string    $software
         * @return array    The array to return to the client
         * @api_role        public
         * @api_cache       10w pharFile    Cache for 10 weeks and tag as pharFile.
         */
        function($software) use ($api) {
            return array(
                $software => exec('git log --pretty="%h %ci" -n1 HEAD')
            );
        }
    )->group('software');

    $api->onRead('/download/:software',
        /**
         * Download the :software
         *
         * @param string    $software
         * @return array    The array to return to the client
         * @api_role        public
         * @api_cache       10w pharFile    Cache for 10 weeks and tag as pharFile.
         */
        function($software) use ($api) {
            $file = "../../dist/$software";
            if (file_exists($file)) {
                echo file_get_contents($file);
                exit;
            }
            throw new Exception("'$software' doesn't not exist.");
        }
    )->group('software');

    $api->onCreate('/upload/:software',
        /**
         * Upload a new software :software
         *
         * @param string        $software
         * @return array        The array to return to the client
         * @api_role admin
         * @api_purge_cache     pharFile    Purge the cahce of all the 'pharFile' tagged entries.
         */
        function($software) {
            throw new Exception("Todo");
        }
    );

    /**
     * Update an existing software :software
     *
     * @param string    $software
     * @return array    The array to return to the client
     * @api_role admin
     * @api_purge_cache julien
     */
    $api->onUpdate('/upload/:software', function($software)
    {
        throw new Exception("TODO");
    });

    echo $api->run();

} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
}
