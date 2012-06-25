<?php

namespace Zenya\Api\Util;

use Zenya\Api;

class Compile
{
    const DEFAULT_PHAR_FILE = 'sleepover.phar';

    protected $version;

    /**
     * Compiles the source code into one single Phar file.
     *
     * @param string $pharFile Name of the output Phar file
     */
    public function compile($pharFile = self::DEFAULT_PHAR_FILE)
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        if( $log = system('git log --pretty="%h %ci" -n1 HEAD') ) {
            $this->version = trim($log);
        } else {
            throw new \RuntimeException('The git binary cannot be found.');
        }

        $phar = new \Phar($pharFile, 0, 'sleepover.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        // all the files
        $root = __DIR__.'/../../../../..';
        foreach ( array('src/php', 'vendor/php') as $dir) {
            $it = new \RecursiveDirectoryIterator("$root/$dir");
            foreach(new \RecursiveIteratorIterator($it) as $file) {
                if (
                    $file->getExtension() == 'php'
                    && !preg_match ('@/src/php/Zenya/Api/Util/Compile.php$@', $file->getPathName())
                ) {
                    $path = $file->getPathName();
                    $this->addFile($phar, $path);
                    #echo $file->getPathName() . "\n";
                }
            }
        }

        $this->addFile($phar, new \SplFileInfo($root.'/LICENSE.txt'), false);
        #$this->addFile($phar, new \SplFileInfo($root.'/vendor/autoload.php'));
        #$this->addFile($phar, new \SplFileInfo($root.'/vendor/composer/ClassLoader.php'));
        #$this->addFile($phar, new \SplFileInfo($root.'/vendor/composer/autoload_namespaces.php'));
        #$this->addFile($phar, new \SplFileInfo($root.'/vendor/composer/autoload_classmap.php'));

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        // $phar->compressFiles(\Phar::GZ);

        echo 'The new phar has ' . $phar->count() . " entries\n";

        unset($phar);
        chmod($pharFile, 0777);
    }

    protected function addFile($phar, $path, $strip = true)
    {
        $path = realpath($path);

        $localPath = str_replace(
            dirname(dirname(dirname(dirname(dirname(__DIR__)))))
            . DIRECTORY_SEPARATOR,
            '',
            realpath($path)
        );
        #$localPath = str_replace('src/php'.DIRECTORY_SEPARATOR, '', $localPath);

        echo $localPath . " ($path)" . PHP_EOL;

        $content = file_get_contents($path);
        if ($strip) {
            $content = self::stripWhitespace($content);
        }

        $content = preg_replace("/const VERSION = '.*?';/", "const VERSION = '".$this->version."';", $content);

        $phar->addFromString('/'.$localPath, $content);
    }

    protected function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
/**
 * Sleepover.phar
 */

Phar::mapPhar('sleepover.phar');

#require 'phar://sleepover.phar/';

// set_include_path(get_include_path()
//     .PATH_SEPARATOR.'phar://'.__FILE__.'/src/php'
//     .PATH_SEPARATOR.'phar://'.__FILE__.'/vendor/php'
//     );
// spl_autoload_register();

require 'phar://sleepover.phar/vendor/php/psr0.autoloader.php';

/*
define('APP_TOPDIR', 'phar://sleepover.phar/src/php');
define('APP_LIBDIR', 'phar://sleepover.phar/vendor/php');
#define('APP_TESTDIR', realpath(__DIR__ . '/../tests/unit-tests/php'));

require APP_LIBDIR . '/psr0.autoloader.php';

psr0_autoloader_searchFirst(APP_LIBDIR);
psr0_autoloader_searchFirst(APP_TOPDIR);
#psr0_autoloader_searchFirst(APP_TESTDIR);
*/

/*
function __autoload($class)
{
    include 'phar://sleepover.phar/' . str_replace('_', '/', $class) . '.php';
}
try {
    Phar::mapPhar('me.phar');
    include 'phar://me.phar/startup.php';
} catch (PharException $e) {
    echo $e->getMessage();
    die('Cannot initialize Phar');
}
*/

if ('cli' === php_sapi_name() && basename(__FILE__) === basename($_SERVER['argv'][0]) && isset($_SERVER['argv'][1])) {
    switch ($_SERVER['argv'][1]) {
        case 'update':
            $remoteFilename = 'http://sleepover.dev/get/sleepover.phar';
            $localFilename = __DIR__.'/sleepover.phar';

            file_put_contents($localFilename, file_get_contents($remoteFilename));
            break;

        case 'check':
            $latest = trim(file_get_contents('http://sleepover.dev/get/version'));

            if ($latest != Server::VERSION) {
                printf("A newer Sleepover version is available (%s).\n", $latest);
            } else {
                print("You are using the latest Sleepover version.\n");
            }
            break;

        case 'test':
            print file_get_contents("phar://sleepover.phar/src/php/Zenya/Api/Server");

            printf("apiVersion: %s\n", Phar::apiVersion());
            break;

        case 'extract':
            $p = new Phar('sleepover.phar');
            $p->extractTo('extracted');
            break;

        case 'ls':
            $p = new Phar('sleepover.phar');
            foreach(new \RecursiveIteratorIterator($p) as $it) {
                echo $it->getPathName() . PHP_EOL;
            }
            break;

        case 'r':
            print file_get_contents("phar://sleepover.phar/" . $_SERVER['argv'][2]);
            break;


        case 'version':
            $version = Zenya\Api\Server::VERSION;
            printf("Sleepover version %s\n", $version);
            break;

        default:
            printf("Unknown command '%s' (available commands: version, check, and update).\n", $_SERVER['argv'][1]);
    }

    exit(0);
}

__HALT_COMPILER();
EOF;
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * Based on Kernel::stripComments(), but keeps line numbers intact.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the whitespace removed
     */
    public static function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }
}
