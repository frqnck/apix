<?php
require_once __DIR__ . '../../../build/zenya-api-server.phar';

#echo $_SERVER["SCRIPT_FILENAME"];
#exit;

# Test server
try {
    $api = new Zenya\Api\Server;

    $api->onRead('/download/:software',
        /**
         * Dowload the software
         *
         * @param string $software
         * @return array  The array to return to the client
         * @api_role admin
         */
        function($software) use ($api)
        {
            return array($software);
        }
    )->group('software');

    $api->onRead('/version/:software',
        /**
         * List all the keywords...
         *
         * @param string $software
         * @return array  The array to return to the client
         * @api_role admin
         */
        function($software) use ($api)
        {
            return array('version for ' . $software);
        }
    )->group('software');

    echo $api->run();

} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
}
exit;