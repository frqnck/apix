<?php

namespace Apix\Console;

use Apix\Console,
    Apix\Server,
    Apix\Input;

class Main extends Console
{

    protected $version;
    protected $version_program;

    protected $src;
    protected $src_file = 'apix.phar';
    protected $src_url;

    protected $phar_name = null;

    public function __construct(array $options = null)
    {
        $this->src_url = 'http://api.ouarz.net/v1/%s/%s/in/%s';

        #$this->src = realpath(__DIR__ . '/../../../../../');
        $this->src = realpath(__DIR__ . '/../../../../');

        $this->version = Server::VERSION;
        $this->version_program =
            sprintf('Apix Server %s by Franck Cassedanne.', $this->version);

        parent::__construct($options);
    }

    public function setPharName($name)
    {
        $this->phar_name = $name;
        $this->src = $name;
    }

    public function setSrcFile($name)
    {
        $this->src_file = $name;
    }

    public function run()
    {
        $args = $this->getArgs();
        $args[0] = 'php ' . $args[0];

        $cmd = empty($args[1])
                ? '--help'
                : $args[1];

        $this->out($this->version_program . PHP_EOL .PHP_EOL);

        switch ($cmd):
            case '-r': case '--readme':
                $this->out('README' . PHP_EOL . PHP_EOL, 'bold', 'underline');
                $this->out(
                    file_get_contents($this->src . '/README.md')
                );
                $this->out();
            break;

            case '--version':
                exit(0);
            break;

            case '--extractdist': case '-e':
                $src = $this->src . '/src/data/distribution/';
                $dest = $_SERVER['PWD'];
                try {
                    $it = new \DirectoryIterator($src);
                    $this->out("Latest distribution files: " . PHP_EOL, 'green');

                    foreach ($it as $file) {
                        if ($file->isFile()) {
                            $this->out(" --> " . $file . PHP_EOL, "red");
                            file_put_contents(
                                $dest . '/' . $file,
                                file_get_contents($src . '/' .$file)
                            );
                        }
                    }
                } catch (\Exception $e) {
                    $this->outputError($e);
                }
                $this->out(PHP_EOL . "have been copied into:" . PHP_EOL, 'green');
                $this->out(" --> " . $dest . PHP_EOL .PHP_EOL, 'red');
                $this->out("Manually rename each files from '*.dist.php' to '*.php' to use them." . PHP_EOL);
                $this->out("e.g." . PHP_EOL . "cp -i config.dist.php projectname-config.php" . PHP_EOL, 'blue');
            break;

            case '-c': case '--check':

                try {
                    $url = sprintf($this->src_url, 'version', $this->src_file, $this->version);

                    if ($this->verbose) {
                        $this->outRegex("Contacting...\n<brown>${url}</brown>\n\n");
                    }
                    $response = trim($this->getContents($url));

                    $input = new Input\Json;
                    $r = $input->decode($response, true);
                    if ($this->verbose > 2) {
                        print_r($r);
                    }

                    $latest = $r['apix']['version'][$this->src_file]['latest'];
                    if (empty($latest)) {
                        throw new \Exception("Something, somewhere failed!");
                    }

                    if( version_compare($this->version, $latest, '>=') != 1) {
                        $this->out(sprintf("A newer version is available (%s).", $latest));                        
                        $this->outRegex(sprintf("\nTo update, run: <brown>%s --selfupdate</brown>\n",  $args[0]));
                    } else {
                        $this->out("You are using the latest version.");
                    }
                } catch (\Exception $e) {
                    $this->outputError($e);
                }
            break;

            case '--selfupdate':
                $url = sprintf($this->src_url, 'download', $this->src_file, $this->version);
                $dest = $_SERVER['argv'][0];

                if($this->verbose > 2) {
                    $this->out(' --> ' . $dest);
                    $this->out();
                }

                $this->retrieveUpdate($url, $dest);
            break;

            case '-s': case '--syscheck':
                $syscheck = new SystemCheck;
                $syscheck->setArgs(array('--all', '--no-credits'));
                $syscheck->run();
                break;

            case '--license':
                $this->out(file_get_contents($this->src .'/LICENSE.txt'));
            break;

            case '-i': case '--info':
                phpinfo();
            break;

            case '-h': case '--help':
                $this->help();
            break;

            case '-t': case '--tests':
                $cmd = 'phpunit';
                if($this->verbose) {
                    $cmd .= ' --verbose';
                }
                if($this->verbose > 2) {
                    $cmd .= ' --debug';
                }
                if(false == $this->no_colors) {
                    $cmd .= ' --colors';
                }
                $cmd .= ' --bootstrap src/tests/unit-tests/pharstrap.php';
                $cmd .= ' -c src/tests/unit-tests/pharunit.xml';
                system($cmd);
            break;

            default:
                $this->out('Error: ', $args[1], 'bold', 'red');
                $this->out(sprintf('unknown option/command "%s".' . PHP_EOL, $args[1]), 'red');
                $this->out(sprintf('You should try "%s --help".', $args[0]), 'green');

        endswitch;

        $this->out();

        exit(0);
    }

    public function help()
    {
        $args = $this->getArgs();
        $args[0] = 'php ' . $args[0];

        $this->outRegex(
<<<HELP
<bold>Usage:</bold> <brown>{$args[0]}</brown> [OPTIONS]\r\n
<bold>Options:</bold>\r
   --readme <brown>|</brown> -r\t<brown>Display the README file</brown>
   --extractdist <brown>|</brown> -e\t<brown>Extract the latest distribution data</brown>
   --check <brown>|</brown> -c\t\t<brown>Check for updates</brown>
   --selfupdate\t\t<brown>Upgrade Apix to the latest version available</brown>
   --version\t\t<brown>Display the version information and exit</brown>
   --info <brown>|</brown> -i\t\t<brown>PHP information and configuration</brown>
   --license\t\t<brown>Display the software license</brown>
   --syscheck <brown>|</brown> -s\t<brown>Run a system check</brown>
   --tests <brown>|</brown> -t\t\t<brown>Run some unit & functional tests</brown>
   --no-colors\t\t<brown>Don't use colors in the outputs</brown>
   --verbose <brown>|</brown> -v\t<brown>Add some verbosity to the outputs.\n\t\t\tMultiple -v options increase the verbosity.</brown>
   --help <brown>|</brown> -h\t\t<brown>Display this help</brown>\n\n
HELP
        );
        exit;
    }

    public function outputError(\Exception $e)
    {
        $this->out('Error: ', 'bold', 'red');
        $this->out("\tUnable to proceed.\n");

        if ($this->verbose > 1) {
            $this->out("\t" . $e->getMessage() . "\n");
        }

        $errors = error_get_last();
        if ($this->verbose == 3 && null !== $errors) {
            $this->out();
            print_r($errors);
        }

        $this->out();

        exit;
    }

    public function getContents($url, $method='GET', $body=null)
    {
        $opts = array('http' => array(
            'method'  => $method,
            'header'  => "Content-Type: text/xml\r\n".
            'Authorization: Basic ' . base64_encode($this->src_file . ":sesame") . "\r\n",
            'content' => $body,
            'timeout' => 60
            )
        );

        $ctx  = stream_context_create($opts);
        $body = @file_get_contents($url, false, $ctx);
        if(isset($http_response_header)) {
            $code = substr($http_response_header[0], 9, 3);
            if (floor($code/100)>3) {
                throw new \Exception($http_response_header[0]);
            }
            return $body;
        } else {
            throw new \Exception("Request failed.");
        }

        return null;
    }

    protected function retrieveUpdate($url, $dest)
    {
        $temp = basename($dest, '.phar').'-temp.phar';

        try {
            copy($url, $temp);
            chmod($temp, 0777 & ~umask());

            // test the phar validity
            $phar = new \Phar($temp);
            // free the variable to unlock the file
            unset($phar);
            rename($temp, $dest);
        } catch (\Exception $e) {
            if (!$e instanceof \UnexpectedValueException&& !$e instanceof \PharException) {
                throw $e;
            }
            unlink($temp);
            $this->outputError('The dowloading ('.$e->getMessage().').');
            $output->out('Please re-run this again.');
        }

        $this->out($this->src_file . " has been updated.");
    }

    //     ini_set('phar.readonly', 0);
    //     if(true) {
    //         $this->out("Please, re-run this as: ");
    //         $this->out();
    //         $this->out('$ php -d phar.readonly=0 ' . $args[0], 'brown');
    //         exit(0);
    //     }
    //     if(false !== @file_put_contents($local, $this->getContents($url))) {
    //         $this->out($this->src_file . " has been updated.");
    //     } else {
    //          throw new \Exception;
    //     }
    // } catch (\Exception $e) {
    //     $this->outputError($e);


}
