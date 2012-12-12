<?php
//namespace Apix;

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
    $api = new Apix\Server;

    $api->onRead('/articles/:filters',
        /**
         * Retrieve a list of articles
         * Just an example
         * @param     string  $name  Your name.
         * @return    array
         * @api_auth  groups=clients,reselers users=franck
         * @api_cache ttl=1day tags=author
         */
         function($filters=null) {
            // some logic
            return array('$results');
        });


    $api->onRead('/test/:id', //<[[:digit:]]{1,3}>

        /**
         * Read an enti2ty id (title).
         * Some documentation for this test method (description).
         * fsdfs .asdas das
         *
         * @param   integer $id Some description describing the parameter.
         * @param   integer $testd Some documentation for this param
         * @param   string $testd Some documentation for this param
         * @param   array $tests Some documentation for this param
         * @example Consider the following example: <pre>http://api.domain.tld/hello</pre>
         * @see See this.
         * @see Check this document: <url>http://some_ref</url>
         * @todo test ttest
         * @toc The toc entry (@toc).
         *
         * @return  boolean Indicates whether the action was successful.
         *
         * @api_auth    groups=admin users=franck
         * @api_cache   ttl=5mins tags=tag1,tag2,tag3 flush=tag9,tag10
         */
        function($id) use ($api) {
            $params = $api->request->getBody();

            return array(
                'test'  => ' 111value11s',
                #'xml' =>array('&"\'<> ?|\\-_+=@£$€*/":;[]{}'),
                'body'  => $api->request->getBody(),
                //'params'    => $api->getBodyData()
            );
        }
    );

    $api->onRead('/test/me/:t',

        /**
         * POST /test/:id
         * Some documentation for this test method.
         *
         * @param   integer $id Some description describing the parameter.
         *                      Another line.
         * @param   integer $test
         * @return  array  Some infos about the returned data.
         *
         * @api_auth    groups=admin users=franck
         * @api_cache   ttl=5min tags=tag1,tag2,tag3,v1 flush=tag9,tag10
         * @toc The title for toc (overide).
         */
        function($t) {

$start = microtime(true);
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: text/xml');

$x = new \XmlWriter();
$x->openURI('php://output');
$x->startDocument('1.0', 'utf-8');
$x->startElement('Products');
#$x->setIndent(true);
for ($i = 0; $i < 10; $i++) {
    $x->startElement('Word');
    $x->writeAttribute('Id', $i);
    $x->writeAttribute('Value', md5(uniqid()));
    $x->endElement();
}
$x->endElement();
$x->flush();

// $stop = microtime(true);
// $seconds = $stop - $start;
// echo "Start: " . $start . PHP_EOL;
// echo "Stop: " . $stop . PHP_EOL;
// echo "Seconds: " . $seconds . PHP_EOL;
// echo "Memory peak: " . memory_get_peak_usage() / 1048576 . 'MB' . PHP_EOL;

            exit;

            return $words;
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
