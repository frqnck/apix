<?php

/**
 * Copyright (c) 2011 Franck Cassedanne, Zenya.com
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
 * @package     Zenya\Api
 * @subpackage  Server
 * @author      Franck Cassedanne <fcassedanne@zenya.com>
 * @copyright   2011 Franck Cassedanne, Zenya.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://zenya.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Zenya\Api;

use Zenya\Api\Console;

class SystemCheck extends Console
{

    public $software_name = "zenya-api-server";

    function help()
    {
        if(array_intersect(array('-h', '--help'), $this->args)) {
            echo <<<HELP
Usage: {$this->args[0]} [options]

Options:
   --help | -h      Display this help.

   --colors | -c    Use colors in output.

   --required       Run only the required checks.

   --optionals       Run only the optionals checks.

   --all            Run all the checks (default).


HELP;
        exit;
        }
    }

    function run($quiet=false)
    {        
        $this->out(PHP_EOL);
        $this->out("\tSystem check for " . $this->software_name . "\t", 'black', 'on_cyan');
        $this->out(PHP_EOL . PHP_EOL);

        $this->help();

        if(!array_intersect(array('--required', '--optionals'), $this->args)) {
            $this->args[] = '--all';
        }

        $this->display(
            'Required to pass: ', 
            $this->getRequired(),
            array('--required', '--all')
        );

        $this->out(PHP_EOL . PHP_EOL);

        $this->display(
            'Recommended to pass (optionals): ',
            $this->getOptionals(),
            array('--optionals', '--all')
        );

        $this->out(PHP_EOL . PHP_EOL);

        // if() {
        //     $this->out(PHP_EOL . PHP_EOL . "All your PHP Settings are extensions are fine. Well done, you are ready to roll!" . PHP_EOL . PHP_EOL, 'success');
        // } else {
        //     $this->out(PHP_EOL . PHP_EOL . "All your PHP Settings are extensions are fine. Well done, you are ready to roll!" . PHP_EOL . PHP_EOL, 'success');
        // }
    }

    function display($title, array $checks, array $args)
    {
        $this->out($title, 'bold');
        if(array_intersect($args, $this->args)) {
            foreach($checks as $key => $check) {
                $this->out(PHP_EOL . PHP_EOL);
                $this->check($key, $check);
            }
        } else {
            $this->out('skipped, not in the run.', 'failed');
        }
    }

    public function check($key, $check)
    {
        $this->out("   - {$key}: ");
        if($check['fail'] === true) {
            $this->out('failed', 'failed');
            $this->out(PHP_EOL);
            foreach($check['msgs'] as $msg) {
                $this->out(PHP_EOL);
                $this->out("     " . $msg, 'green');
            }
        } else {
            $this->out('pass', 'success');
        }
    }

    function getRequired()
    {
        $suhosin = ini_get('suhosin.executor.include.whitelist');

        $required = array(
            'PHP version' => array(
    			'fail' => version_compare(PHP_VERSION, '5.3.2', '<'),
    			'msgs' => array(
                    "The version of PHP (" . PHP_VERSION .") installed is too old.",
                    "You must upgrade to PHP 5.3.2 or higher."
                )
            ),
            'Phar support' => array(
                'fail' => !extension_loaded('Phar'),
                'msgs' =>array(
                    "The phar extension is missing.",
                    "Install it or recompile PHP without using --disable-phar"
                )
            ),
            'Suhosin' => array(
    			'fail' => false !== $suhosin && false === stripos($suhosin, 'phar'),
    			'msgs' => array(
                    "The suhosin.executor.include.whitelist setting is incorrect.",
                    "Add the following to the end of your 'php.ini' or 'suhosin.ini':",
                    "    suhosin.executor.include.whitelist = phar " . $suhosin
                )
            ),
            'detect_unicode' => array(
                'fail' => ini_get('detect_unicode'),
                'msgs' => array(
                    "This setting must be disabled.",
                    "Add the following to the end of your 'php.ini':",
                    "    detect_unicode = Off"
                )
            ),
            'allow_url_fopen' => array(
    			'fail' => !ini_get('allow_url_fopen'),
    			'msgs' => array(
                    "The allow_url_fopen setting is incorrect.",
                    "Add the following to the end of your 'php.ini':",
                    "    allow_url_fopen = On"
                )
            ),
            'ionCube loader disabled' => array(
    			'fail' => extension_loaded('ionCube Loader'),
    			'msgs' => array(
                    "The ionCube Loader extension is incompatible with Phar files.",
                    "Remove this line (path may be different) from your 'php.ini':",
                    "    zend_extension = /usr/lib/php5/20090626+lfs/ioncube_loader_lin_5.3.so"
                )
            )
        );

        return $required;
    }

    function getOptionals()
    {
        // sigchild
        ob_start();
        phpinfo(INFO_GENERAL);
        $phpinfo = ob_get_clean();
        preg_match('{Configure Command(?: *</td><td class="v">| *=> *)(.*?)(?:</td>|$)}m', $phpinfo, $config);

        $optionals = array(
            // APC
            'apc_cli' => array(
                'fail' => ini_get('apc.enable_cli'),
                'msgs' => array(
                    "The apc.enable_cli setting is incorrect.",
                    "Add the following to the end of your 'php.ini':",
                    "    apc.enable_cli = Off"
                ),
            ),

            // sigchild
            'sigchild' => array(
                'fail' => false !== strpos($config[1], '--enable-sigchild'),
                'msgs' => array(
                    "PHP was compiled with --enable-sigchild which can cause issues on some platforms.",
                    "Recompile it without this flag if possible, see also:",
                    "    https://bugs.php.net/bug.php?id=22999"
                )
            )
        );
        
        return $optionals;
    }

    public function out($msg, $type=null)
    {
        $msg = str_replace("{software.name}", $this->software_name, $msg);

        switch ($type):
            case 'error':
               parent::out($msg, 'red');
            break;

            case 'info':
               parent::out($msg, 'green');
            break;

            case 'success':
               parent::out($msg, 'black', 'green');
            break;

            case 'failed':
               parent::out($msg, 'black', 'red');
            break;


            default:
               parent::out(func_get_args());
        endswitch;
    }

}