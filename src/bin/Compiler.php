<?php
#namespace Zenya\Api\Util;

#use Zenya\Api;

class Compiler
{
    const DEFAULT_PHAR_FILE = 'zenya-api-server.phar';

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

        $phar = new \Phar($pharFile, 0, $pharFile);
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        // start buffering. Mandatory to modify stub.
        $phar->startBuffering();

        // all the files
        $root = __DIR__ . '/../..';
        foreach ( array('src/php', 'vendor/php') as $dir) {
            $it = new \RecursiveDirectoryIterator("$root/$dir");
            foreach(new \RecursiveIteratorIterator($it) as $file) {
                if (
                    $file->getExtension() == 'php'
                    && !preg_match ('@/src/php/Zenya/Api/Util/Compile.php$@', $file->getPathName())
                ) {
                    $path = $file->getPathName();
                    $this->addFile($phar, $path);
                }
            }
        }

        $this->addFile($phar, new \SplFileInfo($root . '/LICENSE.txt'), false);
        $this->addFile($phar, new \SplFileInfo($root . '/README.md'), false);
        $this->addFile($phar, new \SplFileInfo($root . '/src/data/config.dist.php'), false);

        // get the stub
        $stub = preg_replace("@{VERSION}@", $this->version, $this->getStub());
        $stub = preg_replace("@{PHAR}@", $pharFile, $stub);
        $stub = preg_replace("@{URL}@", 'http://zenya.dev/get', $stub);
        $stub = preg_replace("@{BUILD}@", gmdate("Y-m-d\TH:i:s\Z"), $stub);

        // Add the stub
        $phar->setStub( $stub );

        $phar->stopBuffering();

        #$phar->compressFiles(\Phar::GZ);

        echo 'The new phar has ' . $phar->count() . " entries\n";

        unset($phar);
        chmod($pharFile, 0777);
        rename($pharFile, __DIR__ . '/../../build/' . $pharFile);
    }

    protected function addFile($phar, $path, $strip = true)
    {
        $path = realpath($path);

        $localPath = str_replace(
            dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR,
            '',
            realpath($path)
        );
        #$localPath = str_replace('src/php'.DIRECTORY_SEPARATOR, '', $localPath);
        #echo $localPath . " ($path)" . PHP_EOL;

        $content = file_get_contents($path);
        if ($strip) {
            $content = self::stripWhitespace($content);
        }

        // TODO review this!!!
        $content = preg_replace("/const VERSION = '.*?';/", "const VERSION = '".$this->version."';", $content);

        #$localPath = strtolower($localPath);
        $phar->addFromString('/' . $localPath, $content);
    }

    /*
     * Returns the stub
     */
    protected function getStub()
    {
        // #!/usr/bin/env php
        return <<<'STUB'
<?php
/**
 * Zenya Api Server
 *
 * @version {VERSION}
 * @build {BUILD}
 */
try {
    Phar::mapPhar('{PHAR}');

    $loc = 'phar://{PHAR}';
    define('APP_LIBDIR', $loc . '/vendor/php');
    define('APP_TOPDIR', $loc . '/src/php');
    //define('APP_TESTDIR', $loc . '/tests/unit-tests/php');

    require APP_LIBDIR . '/psr0.autoloader.php';
    #require_once APP_TOPDIR . '/Zenya/Api/Server.php';

    psr0_autoloader_searchFirst(APP_LIBDIR);
    psr0_autoloader_searchFirst(APP_TOPDIR);
    //psr0_autoloader_searchFirst(APP_TESTDIR);

    spl_autoload_register(function($name){
        #include APP_TOPDIR .'/' . str_replace('\\', DIRECTORY_SEPARATOR, $name) . '.php';
        $file = '/' . str_replace('\\', DIRECTORY_SEPARATOR, $name).'.php';
        $path = APP_TOPDIR . $file;
        if (file_exists($path)) {
            require $path;
        }
    });

} catch (Exception $e) {
    echo $e->getMessage();
    die('Cannot initialize Phar');
}

if ('cli' === php_sapi_name() && basename(__FILE__) === basename($_SERVER['argv'][0]))
{
    $version = Zenya\Api\Server::VERSION;
    $versionStr = sprintf("Zenya API Server %s by Franck Cassedanne", $version);

    $cmd = empty($_SERVER['argv'][1])
            ? '--help'
            : trim($_SERVER['argv'][1]);

    switch ($cmd):
        case '--extractdist':
            try {
                $config = 'phar://{PHAR}/src/data/config.dist.php';
                $local  = __DIR__ . '/config.dist.php';
                file_put_contents($local, file_get_contents($config));
            } catch (Exception $e) {
                echo 'Error: Unable to proceed. ' . $e->getMessage();
            }
            echo "Latest distribution files were copied into:" . PHP_EOL;
            echo __DIR__ . PHP_EOL .PHP_EOL;
            echo "Manually rename each files from '*.dist.php' to '*.php' to use them." . PHP_EOL;
            echo "e.g. cp -i config.dist.php config.php";
            break;

        case '--selfupdate':
            try{
                $remote = '{URL}/{PHAR}';
                $local  = __DIR__ . '/sleepover.phar';

                file_put_contents($local, file_get_contents($remote));
            } catch (Exception $e) {
                echo 'Error: Unable to proceed. ' . $e->getMessage();
            }
            break;

        case '-t': case '--tests':
                system('phpunit --colors tests/phar-test.php');
            break;

        case '-c': case '--check':
            try{
                $latest = trim(file_get_contents('{URL}/{PHAR}/version'));

                if ($latest != $version) {
                    printf("A newer version is available (%s).", $latest);
                } else {
                    print("You are using the latest version.");
                }
            } catch (Exception $e) {
                echo 'Error: Unable to proceed. ' . $e->getMessage();
            }
            break;

        case '-l': case '--license':
                echo file_get_contents('phar://{PHAR}/LICENSE.txt');
            break;

        case '-r': case '--readme':
                echo file_get_contents('phar://{PHAR}/README.md');
            break;

        case '-i': case '--info':
                phpinfo();
            break;

        case '-v': case '--version':
                echo $versionStr . PHP_EOL;
            break;

        case '-h': case '--help':
            echo <<<HELP
{$versionStr}

Usage: {$_SERVER['argv'][0]} [command]

Commands:
   --readme | -r    Display the README file.

   --extractdist    Extract the latest distribution data.

   --selfupdate     Upgrade the server to the latest version available.

   --check | -c     Check the version.

   --version | -v   Display the version information and exit.

   --help | -h      Display this help.

   --info | -i      PHP information and configuration.

   --license | -l   Display the software license.

   --tests | -t     Run some tests.

HELP;
            break;

        default:
            printf("Error: Unknown command '%s' (try \"%s --help\" for a list of available commands).", $_SERVER['argv'][1], $_SERVER['argv'][0]);

    endswitch;

    echo PHP_EOL;

    exit(0);
}

__HALT_COMPILER();
STUB;
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