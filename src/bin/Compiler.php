<?php
/**
 * Copyright (c) 2011 Franck Cassedanne
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Zenya nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Apix
 * @subpackage  Console
 * @author      Franck Cassedanne <franck@cassedanne.com>
 * @copyright   2012 Franck Cassedanne
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 * @link        http://zenya.github.com
 * @version     @package_version@
 */

#namespace Zenya\bin;
#use Apix;

class Compiler
{
    const DEFAULT_PHAR_FILE = 'apix.phar';

    protected $version;
    protected $verbose = 3;

    protected $paths_to_skip = array(
        'src/php/Apix/Plugins/Manual.php',
        'src/php/Apix/Plugins/Streaming.php',
        'src/php/Apix/View',
        'src/php/Apix/Plugins/Logger.php',
        'src/data/config_dev.php'
    );

    public function isSkippedPath($path_name)
    {
        foreach ($this->paths_to_skip as $v) {
            if (false !== strrpos($path_name, $v)) {
                return true;
            }
        }
    }

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

        // set version
        if (!isset($_SERVER['argv'][1])) {
            echo 'Usage: ' . $_SERVER['argv'][0] . ' version_string' . PHP_EOL;
            exit;
        }
        $this->version = $_SERVER['argv'][1];
        echo "Processing $pharFile-" . $this->version . PHP_EOL;

        $phar = new \Phar($pharFile, 0, $pharFile);
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        // start buffering. Mandatory to modify stub.
        $phar->startBuffering();

        // all the files
        $root = __DIR__ . '/../..';
        foreach ( array('src/php', 'vendor/php', '/src/data') as $dir) {
            $it = new \RecursiveDirectoryIterator("$root/$dir");
            foreach (new \RecursiveIteratorIterator($it) as $file) {
                if (
                    $file->getExtension() == 'php'
                    && !$this->isSkippedPath($file->getPathname())
                ) {
                    $path = $file->getPathname();
                    $this->addFile($phar, $path, ($dir != '/src/data' ? true : false));
                }
            }
        }

        $this->addFile($phar, new \SplFileInfo($root . '/LICENSE.txt'), false);
        $this->addFile($phar, new \SplFileInfo($root . '/README.md'), false);
        #$this->addFile($phar, new \SplFileInfo($root . '/src/data/distribution/config.dist.php'), false);

        if ( ! $latest_git = trim(exec('git log --pretty="%h %ci" -n1 HEAD')) ) {
            throw new \RuntimeException('The git binary cannot be found.');
        }

        // get the stub
        $stub = str_replace("@package_version@", $this->version, $this->getStub());
        $stub = str_replace("{GIT}", $latest_git, $stub);
        $stub = str_replace("{BUILD}", gmdate("Ymd\TH:i:s\Z"), $stub);
        $stub = str_replace("{PHAR}", $pharFile, $stub);

        // Add the stub
        $phar->setStub($stub);

        $phar->stopBuffering();

        $phar->compressFiles(\Phar::GZ);

        echo 'The new phar has ' . $phar->count() . ' entries.' . PHP_EOL;
        unset($phar);

        chmod($pharFile, 0777);
        rename($pharFile, __DIR__ . '/../../dist/' . $pharFile);

        echo 'Created in ' . realpath(__DIR__ . '/../../dist/') . PHP_EOL;
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
        if($this->verbose > 2) echo $localPath . " ($path)" . PHP_EOL;

        $content = file_get_contents($path);
        if ($strip) {
            $content = self::stripWhitespace($content);
        }

        // TODO: review versioning!!!
        #$content = preg_replace("/const VERSION = '.*?';/", "const VERSION = '".$this->version."';", $content);

        $content = preg_replace("/@package_version@/", $this->version, $content);

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
 * Copyright (c) 2012 Franck Cassedanne
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Apix nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Apix
 * @author      Franck Cassedanne <franck@cassedanne.com>
 * @copyright   2012 Franck Cassedanne
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version     @package_version@
 * @build       {GIT} / {BUILD}
 */
try {
    Phar::mapPhar('{PHAR}');
    spl_autoload_register(function($name){
        $file = '/' . str_replace('\\', DIRECTORY_SEPARATOR, $name).'.php';
        $path = 'phar://{PHAR}/src/php' . $file;
        if (file_exists($path)) require $path;
    });
} catch (Exception $e) {
    die('Error: cannot initialize - ' . $e->getMessage());
}
if ('cli' === php_sapi_name() && basename(__FILE__) === basename($_SERVER['argv'][0])) {
    $cli = new Apix\Console\Main;
    $cli->setPharName('phar://{PHAR}');
    $cli->run();
    exit(0);
}
__HALT_COMPILER();
STUB;
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     * Based on Kernel::stripComments(), but keeps line numbers intact.
     *
     * @param  string $source A PHP string
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
