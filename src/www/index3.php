<?php
require_once __DIR__ . '../../../dist/zenya-api-server.phar';

#echo $_SERVER["SCRIPT_FILENAME"];
#exit;

$config = array();

# Test server
try {
    $api = new Zenya\Api\Server(require "../../src/data/config.dist.php");

    $api->onRead('/version/:software',
        /**
         * Returns the last version of the :software  
         *
         * @param string    $software
         * @return array    The array to return to the client
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
         * @api_role        admin
         */
        function($software) use ($api) {
            return array($software => 'dl');
        }
    )->group('software');

    echo $api->run();

} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
}
exit;
