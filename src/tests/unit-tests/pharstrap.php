<?php
// phpunit --bootstrap src/tests/unit-tests/pharstrap.php

define('UNIT_TEST', true);

define('APP_TESTDIR', realpath(__DIR__ . '/php'));

try {
     // Phar::mapPhar('apix.phar');
    spl_autoload_register(function($name){
        $phar = 'phar://' . realpath(__DIR__ . '/../../../dist/apix.phar');

        $file = '/' . str_replace('\\', DIRECTORY_SEPARATOR, $name).'.php';
        // $path = 'phar://apix.phar/src/php' . $file;
        $path = $phar . '/src/php' . $file;

        if (file_exists($path)) require $path;
    });
} catch (Exception $e) {
    die('Error: cannot initialize - ' . $e->getMessage());
}

$app_libdir = realpath(__DIR__ . '/../../../vendor/php');
require_once($app_libdir . '/psr0.autoloader.php');
psr0_autoloader_searchFirst(APP_TESTDIR);






/*
class OutputDebugSignTest extends \PHPUnit_Framework_TestCase
{
    public function bytesProvider()
    {
        return array(
          array('1 B', 1),
          array('1 kB', 1024),
          array('1 MB', 1024*1024),
          array('1 GB', 1024*1024*1024),
          array('1 TB', 1024*1024*1024*1024),
          array('1.49 kB', 1525),
          array('14.89 kilobytes', 15250, true)
        );
    }

    /**
     * @dataProvider bytesProvider
     
    public function testFormatBytesToString($expected, $bytes, $long=false)
    {
        $d = new Apix\Plugin\OutputDebug( array('enable' => true, 'prepend' => true) );

        $this->assertSame(
            $expected,
            Apix\Plugin\OutputDebug::formatBytesToString($bytes, $long)
        );
    }
}

var_dump(xdebug_get_code_coverage());
*/