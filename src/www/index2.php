<?php
namespace Apix;

define('APP_TOPDIR', realpath(__DIR__ . '/../php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../vendor/php'));
define('APP_TESTDIR', realpath(__DIR__ . '/../tests/unit-tests/php'));

// phix
require_once APP_LIBDIR . '/psr0.autoloader.php';

// Composer
define('APP_SRC', realpath(__DIR__ . '/..'));

define('APP_VENDOR', realpath(__DIR__ . '/../../vendor'));
require APP_VENDOR . '/autoload.php';

psr0_autoloader_searchFirst(APP_LIBDIR);
psr0_autoloader_searchFirst(APP_TESTDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);

# Test server
try {
    $api = new Server;

    $api->onRead('/test/:id',

        /**
         * Some short name
         * Some documentation for this test method.
         *
         * @param   integer $id Some description describing the parameter.
         *                      Another line.
         * @param   integer $test
         * @return  array  Some infos about the returned data.
         *
         * @api_auth    groups=admin users=franck
         * @api_cache   ttl=5min tags=tag1,tag2,tag3,v1 flush=tag9,tag10
         * @see Check this document: <url>http://some_ref</url>
         * @internal
         * @example Consider the following example:
         *          <pre>http://api.domain.tld/hello</pre>*WHAT*...
         * @example test
         * @see This is it....
         * @copyright Zenya.com Ltd.
         * @todo test ttest
         * @ignore
         * @toc Some title for toc
         * @id myId
         */
        function($id) use ($api) {
            $params = $api->request->getBody();

            return array(
                'test'  => 'test',
                'body'  => $api->request->getBody(),
                //'params'    => $api->getBodyData()
            );
        }
    );

    $api->onRead('/upload/:what',

        /**
         * Upload something...
         *
         * @return array  The array to return to the client
         *
         * @api_role    admin
         */
        function() use ($api) {
            $params = $api->request->getBody();

            return array(
                'body'      => $api->request->getBody(),
                //'params'    => $api->getBodyData()
            );
        }
    )->group('group1');




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
        * Title of the API method.
        * Description of the API method.
        *
        * @param  type $test test description
        *
        * @return array  The return array
        */
        function($test='default') use ($api) {

            $doc = $api->entity->getDocs();

            return array('List keywords', $doc);
        }

    )->group('group1');


    $api->onRead('/keywordoos/:cat_id/:optional',

        /**
         * List the keywords under a categoy id.
         *
         * @param  string $cat_id blahblah
         * @return array  The return array
         * @check https://github.com/jeremeamia
         * @api_role admin
         */
        function($cat_id, $optional=null) {
            return array('cat_id' => $cat_id, 'optional'=>$optional, 'from'=>__FUNCTION__);
        }
    );

    $api->onCreate('/keywords/:id',

        /**
         * Create doc about the func/closure.
         *
         * @param  string $keyword
         * @return array  The return array
         * @check https://github.com/jeremeamia
         * @api_role admin
         */
        function($keyword, $optional=null) {
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
    // Apix\d( $api->getResources() );

    // Apix\d( Apix\Config::getInstance()->getRoutes() );

} catch (\Exception $e) {
    Exception::startupException($e);
}
exit;
