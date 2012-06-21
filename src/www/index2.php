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
    $api = new Zenya\Api\Server();

    /**
     * List all the keywords.
     *
     * @param  Request $request
     * @return array  The return array
     */
    $api->onRead('/keywords', function()
    {
        return array('List keywords');
    });

    /**
     * Retrieve specified keyword.
     *
     * @param  string $keyword
     * @return array  The return array
     */
	$api->onRead('/keywords/:keyword/:optional',

	    /**
	     * Within the Closure.
	     *
	     * @param  string $keyword
	     * @return array  The return array
	     * @check https://github.com/jeremeamia
	     */
    	function($keyword, $optional=null)
    	{
		    return array('keyword' => $keyword, 'optional'=>$optional);
		}
	);

    // --- UPDATE
    /**
     * test
     */
    $api->onUpdate('/keywords/:id', function($id) {
        return array('PUT some keywordsddd', 'ffffff', 'dsadsdasd', 'dasdasda');
    });

    // --- DELETE
    $api->onDelete('/keywords/:id', function($id) {
        return array('DELETE some keywordsddd', 'ffffff', 'dsadsdasd', 'dasdasda');
    });




    //debug
    echo $api->run();

    Zenya\Api\d(
    	$api->getResources()
    );

} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
}
exit;