#!/usr/bin/env php
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

$c = new CheckSystem;
$c->run();

class Console
{
    protected $switches = array('-c', '--color', '--colour');
    protected $colorize = false;

    protected $start = "\033[%dm%s";
    protected $end = "\033[0m";

    protected $options;

    public function __construct(array $options = null)
    {
        $this->options = null === $options ? $this->getOptions() : $options;

        if(array_intersect($this->switches, $_SERVER['argv'])) {
            $this->colorize = true;
        }
    }

    public function getOptions()
    {
        $options = array_merge(
            // Foreground colors.
            array_combine(
                array('grey', 'red', 'green', 'yellow', 'blue', 'magenta', 'cyan',
                      'white'),
                range(30, 37)
            ),
            // Background colors.
            array_combine(
                array('on_grey', 'on_red', 'on_green', 'on_yellow', 'on_blue',
                      'on_magenta', 'on_cyan', 'on_white'),
                range(40, 47)

            ),
            // Text style attributes. 3 and 6 are not used.
            array_combine(
                array('bold', 'dark', null, 'underline', 'blink', null, 'inverse',
                      'concealed'),
                range(1, 8)
            )
        );
        unset($options['']); // remove the null(s)
        return $options;
    }

    public function out($msg, $styles=null)
    {
        $styles = is_array($msg) ? $msg : func_get_args();
        $msg = array_shift($styles);

        if(false == $this->colorize) {
            return $msg;
        }

        foreach ($styles as $style) {
            if(isset($this->options[$style])) {
                $msg = sprintf($this->start, $this->options[$style], $msg) . $this->end;
            }
        }
        return $msg;
    }

    public function getArgs($default=null)
    {
        return empty($_SERVER['argv'][1])
            ? $default
            : trim($_SERVER['argv'][1]);
    }

}

class CheckSystem extends Console
{

    public $software_name = "zenya-api-server";

    function help()
    {
        if(array_intersect(array('-h', '--help'), $_SERVER['argv'])) {
            echo <<<HELP
Usage: {$_SERVER['argv'][0]} [options]

Options:
   --help | -h      Display this help.

   --colors | -c    Use colors in output.

   --required       Run only the required checks.

   --optonals       Run only the optionals checks.

   --all            Run all the checks (default).


HELP;
        exit;
        }
    }

    function run($quiet=false)
    {

       if (!$quiet) {
                #$this->out(PHP_EOL . PHP_EOL . "All your PHP Settings are extensions are fine. Enjoy!" . PHP_EOL . PHP_EOL, 'success');
       }


        echo PHP_EOL;
        $this->out("\tSystem check for " . $this->software_name . "\t", 'black', 'on_green');
        echo PHP_EOL . PHP_EOL;

        $this->help();

        $args = $_SERVER['argv'];
        if(!array_intersect(array('--required', '--optonals'), $args)) {
            $args[] = '--all';
        }

        $this->out('Required:' . PHP_EOL);
        if(array_intersect(array('--required', '--all'), $args)) {
            $required = $this->checkRequired();
        } else {
            $this->out('All Skipped', 'failed');
        }

        echo PHP_EOL . PHP_EOL;

        $this->out('Optionals:' . PHP_EOL);
        if(array_intersect(array('--optonals', '--all'), $args)) {
            $optionals = $this->checkOptionals();
        } else {
            $this->out('All Skipped', 'failed');
        }

        echo PHP_EOL . PHP_EOL;
    }

    public function check($key, $value, $msgs)
    {
        echo PHP_EOL;
        $this->out(" - {$key}: ");
        if($value === true) {
            $this->out('Failed', 'failed');
            echo PHP_EOL;
            foreach($msgs as $msg) {
                echo PHP_EOL;
                $this->out("   " . $msg, 'green');
            }
        } else {
            $this->out('Okay', 'success');
        }
        echo PHP_EOL;
    }

    function checkRequired()
    {



        $errors = array();
        $msg = array();

        $errors['php version'] = version_compare(PHP_VERSION, '5.3.2', '<');
        $msg['php version'] = array(
            "Version of PHP (" . PHP_VERSION .") installed is too old.",
            "You must upgrade to PHP 5.3.2 or higher."
        );

        $errors['detect_unicode'] = ini_get('detect_unicode');
        $msg['detect_unicode'] = array(
            "This setting must be disabled.",
            "Add the following to the end of your 'php.ini':",
            "    detect_unicode = Off"
            );

        $suhosin = ini_get('suhosin.executor.include.whitelist');
        $errors['suhosin'] = false !== $suhosin && false === stripos($suhosin, 'phar');

        $msg['suhosin'] = array(
            "The suhosin.executor.include.whitelist setting is incorrect.",
            "Add the following to the end of your 'php.ini' or 'suhosin.ini':",
            "    suhosin.executor.include.whitelist = phar " . $suhosin
        );

        $errors['phar'] = !extension_loaded('Phar');
        $msg['phar'] = array(
            "The phar extension is missing.",
            "Install it or recompile PHP without using --disable-phar"
        );

        $errors['allow_url_fopen'] = !ini_get('allow_url_fopen');
        $msg['allow_url_fopen'] = array(
            "The allow_url_fopen setting is incorrect.",
            "Add the following to the end of your 'php.ini':",
            "    allow_url_fopen = On"
        );

        $errors['ioncube'] = extension_loaded('ionCube Loader');
        $msg['ioncube'] = array(
            "The ionCube Loader extension is incompatible with Phar files.",
            "Remove this line (path may be different) from your `php.ini`:",
            "    zend_extension = /usr/lib/php5/20090626+lfs/ioncube_loader_lin_5.3.so"
        );

        foreach($errors as $key => $status) {
            $this->check($key, $status, $msg[$key]);
        }

        if (!empty($errors)) {
            echo PHP_EOL;
            $this->out("Some settings and/or extensions on this machine will have adverse affect.". PHP_EOL. PHP_EOL, 'error');
            $this->out('Make sure that you fix the issues listed below, and run this script again:'. PHP_EOL. PHP_EOL, 'error');
        }
    }

    function checkOptionals()
    {
        $warnings = array();

            // APC
            $warnings['apc_cli'] = ini_get('apc.enable_cli');
            $msg['apc_cli'] = array(
                "The apc.enable_cli setting is incorrect.",
                "Add the following to the end of your 'php.ini':",
                "    apc.enable_cli = Off"
            );

        // sigchild
        ob_start();
        phpinfo(INFO_GENERAL);
        $phpinfo = ob_get_clean();
        if (preg_match('{Configure Command(?: *</td><td class="v">| *=> *)(.*?)(?:</td>|$)}m', $phpinfo, $match)) {
        }

        $warnings['sigchild'] = false !== strpos($match[1], '--enable-sigchild');

        $msg['sigchild'] = array(
            "PHP was compiled with --enable-sigchild which can cause issues on some platforms.",
            "Recompile it without this flag if possible, see also:",
            "    https://bugs.php.net/bug.php?id=22999"
        );

        foreach($warnings as $key => $status) {
            $this->check($key, $status, $msg[$key]);
        }

        if (!empty($warnings)) {
            echo PHP_EOL;
            $this->out("Some settings on your machine may cause instabilities." .PHP_EOL . PHP_EOL, 'error');
            $this->out('If you encounter issues, try to change the following:'. PHP_EOL. PHP_EOL, 'error');
        }


    }

    public function out($msg, $type=null)
    {
        $msg = str_replace("{software.name}", $this->software_name, $msg);

        switch ($type):
            case 'error':
               echo parent::out($msg, 'red');
            break;

            case 'info':
               echo parent::out($msg, 'green');
            break;

            case 'success':
               echo parent::out($msg, 'black', 'green');
            break;

            case 'failed':
               echo parent::out($msg, 'black', 'red');
            break;


            default:
               echo parent::out(func_get_args());
        endswitch;
    }

}