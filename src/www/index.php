<?php
define('APP_TOPDIR', realpath(__DIR__ . '/../php'));
define('APP_LIBDIR', realpath(__DIR__ . '/../../vendor/php'));
define('APP_TESTDIR', realpath(__DIR__ . '/../tests/unit-tests/php'));

require_once(APP_LIBDIR . '/psr0.autoloader.php');

// step 3: add the additional paths to the include path
psr0_autoloader_searchFirst(APP_LIBDIR);
psr0_autoloader_searchFirst(APP_TESTDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);

# Test server

$server = new Zenya\Api\Server;
$server->run();

exit;

// TODO write tes;
$request = new Zenya\Api\Request();

$data = <<<DATA
// RFC 2616 defines 'deflate' encoding as zlib format from RFC 1950,
        // while many applications send raw deflate stream from RFC 1951.
        // We should check for presence of zlib header and use gzuncompress() or
        // gzinflate() as needed. See bug #15305
DATA;

$request->setHeader('content-encoding', 'deflate' );
$request->setBody( gzdeflate($data) );

$request->setHeader('content-encoding', 'gzip' );
$request->setBody(gzencode($data));

#$request = new \HTTP_Request2;

print_r($request);

#print_r($request->getParam('alnum', 'alnum'));

print_r( $request->getBody() );

exit;
#$h = $request->getHeaders();

print_r($h);
