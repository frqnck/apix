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
    $api = new Zenya\Api\Server;

    $api->onRead('/upload/:what',

        /**
         * List all the keywords...
         *
         * @return array  The array to return to the client
         * @api_role admin
         */
        function() use ($api)
        {
            $params = $api->request->getBody();
            return array(
                'body'      => $api->request->getBody(),
                'params'    => $api->getBodyData()
            );
        }
    )->group('testme');




    /**
     * group.
     *
     * @param  string $keyword
     * @return array  The return array
     * @check https://github.com/jeremeamia
     * @api_role admin
     */
    //$api->setGroup('first');

    $api->onRead('/keywords',
        /**
         * List all the keywords...
         *
         * @return array  The return array
         * @api_role admin
         */
        function()
        {
            return array('List keywords');
        }
    )->group('testme');

	$api->onRead('/keywords/:cat_id/:optional',

	    /**
	     * List the keywords under a categoy id.
	     *
	     * @param  string $cat_id blahblah
	     * @return array  The return array
	     * @check https://github.com/jeremeamia
	     * @api_role admin
		 */
		function($cat_id, $optional=null)
		{
		    return array('cat_id' => $cat_id, 'optional'=>$optional, 'from'=>__FUNCTION__);
		}
	);


    $api->onCreate('/keywords/:keyword/:optional',

        /**
         * Create doc about the func/closure.
         *
         * @param  string $keyword
         * @return array  The return array
         * @check https://github.com/jeremeamia
         * @api_role admin
         */
        function($keyword, $optional=null)
        {
            return array('keyword' => $keyword, 'optional'=>$optional, 'from'=>__FUNCTION__);
        }
    );



    $api->onUpdate('/keywords/:id', function($id) {
        return array('PUT some keywordsddd', 'ffffff', 'dsadsdasd', 'dasdasda');
    });

    $api->onDelete('/keywords/:id', function($id) {
        return array('DELETE some keywordsddd', 'ffffff', 'dsadsdasd', 'dasdasda');
    });


    echo $api->run();

    // Zenya\Api\d( $api->getResources() );

    // Zenya\Api\d( Zenya\Api\Config::getInstance()->getRoutes() );


} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
}
exit;