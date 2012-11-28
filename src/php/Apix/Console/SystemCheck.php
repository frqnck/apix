<?php

namespace Apix\Console;

use Apix\Console,
    Apix\Server;

class SystemCheck extends Console
{

    public function help()
    {
        $args = $this->getArgs();
        $args[0] = 'php ' . $args[0];

        $help = <<<HELP
<bold>Usage:</bold> {$args[0]} [OPTIONS]\r\n
<bold>Options:</bold>\r
   --all\t<brown>Run all the checks</brown>\r
   --required\t<brown>Run only the required checks</brown>\r
   --optionals\t<brown>Run only the optionals checks</brown>\r
   --no-colors\t<brown>Don't use colors in the outputs</brown>\r
   --verbose <brown>\tAdd some verbosity to the outputs</brown>\r
   --help <brown>|</brown> -h\t<brown>Display this help</brown>\n\n
HELP;

        echo $this->outRegex($help);
    }

    public function run($quiet=false)
    {
        if( ! $this->hasArgs(array('--no-credit')) ){
            $this->out(
                sprintf("Apix System Check %s by Franck Cassedanne.\n\n", Server::VERSION)
            );
        }

        if (
            !$this->hasArgs(array('--required', '--optionals', '--all'))
        ){
            $this->help();
            exit;
        }

        $this->out("Checking your system...\n\n", 'bold');

        if ( !$this->hasArgs(array('--required', '--optionals')) ) {
            $this->args[] = '--all';
        }

        $required = $this->getRequired();
        $this->display(
            'Minimum requirements (required to pass): ',
            $required,
            array('--required', '--all')
        );

        $this->out(PHP_EOL . PHP_EOL);

        $optionals = $this->getOptionals();
        $this->display(
            'Optionals (recommended to pass): ',
            $optionals,
            array('--optionals', '--all')
        );

        $this->out(PHP_EOL.PHP_EOL);

        $req = true;
        foreach($required as $k=>$v) {
            if($v['fail'] === true) $req = true;
        }

        $opt = false;
        foreach($optionals as $k=>$v) {
            if($v['fail'] === true) $opt = true;
        }

        echo " -----------------------------------------------------------------------\n\n";
        if ($req) {
            $this->out("  <red>Warning!</red> Minimum system requirements not met. Good luck.", 'regex');
        } else if($opt) {
            $this->out("   Except a few optionals, your PHP settings and extensions are fine.\n\n   Well done, you are ready to roll!");
        } else {
            $this->out("   All your PHP Settings and extensions are fine.\n   Well done, you are ready to roll!");
        }
        echo "\n\n -----------------------------------------------------------------------\n";
    }

    public function display($title, array $checks, array $args)
    {
        $this->out($title);
        if ( $this->hasArgs($args) ) {
            foreach ($checks as $key => $check) {
                $this->out(PHP_EOL);
                $this->check($key, $check);
            }
        } else {
            $this->out('skipped, not in the run.', 'failed');
        }
    }

    public function check($key, $check)
    {
        $this->out("   - {$key}", 'brown');
        if ($check['fail'] !== true) {
            if ($this->verbose > 0 && isset($check['verbose'])) {
                $this->out(sprintf(' (%s): ', $check['verbose']));
            }
            $this->out('   [pass]', 'success');
        } else {
            $this->out('   [failed]', 'failed');
            foreach ($check['msgs'] as $msg) {
                $this->out(PHP_EOL);
                $this->out("\t");
                $this->out($msg, 'failed');
            }
        }
    }

    public function getRequired()
    {
        $suhosin = ini_get('suhosin.executor.include.whitelist');

        $required = array(
            'PHP version > 5.3.2' => array(
                'fail' => version_compare(PHP_VERSION, '5.3.2', '<'),
                'verbose' => 'currently '. PHP_VERSION,
                'msgs' => array(
                    "The version of PHP (" . PHP_VERSION .") installed is too old.",
                    "You must upgrade to PHP 5.3.2 or higher."
                )
            ),
            'Phar support' => array(
                'fail' => !extension_loaded('Phar'),
                'verbose' => 'on',
                'msgs' =>array(
                    "The phar extension is missing.",
                    "Install it or recompile PHP without using --disable-phar"
                )
            ),
            'Suhosin' => array(
                'fail' => false !== $suhosin && false === stripos($suhosin, 'phar'),
                'verbose' => 'off or whitelisted',
                'msgs' => array(
                    "The suhosin.executor.include.whitelist setting is incorrect.",
                    "Add the following to the end of your 'php.ini' or 'suhosin.ini':",
                    "    suhosin.executor.include.whitelist = phar " . $suhosin
                )
            ),
            'detect_unicode' => array(
                'fail' => ini_get('detect_unicode'),
                'verbose' => 'off',
                'msgs' => array(
                    "This setting must be disabled.",
                    "Add the following to the end of your 'php.ini':",
                    "    detect_unicode = Off"
                )
            ),
            'allow_url_fopen' => array(
                'fail' => !ini_get('allow_url_fopen'),
                'verbose' => 'on',
                'msgs' => array(
                    "The allow_url_fopen setting is incorrect.",
                    "Add the following to the end of your 'php.ini':",
                    "    allow_url_fopen = On"
                )
            ),
            'ionCube loader disabled' => array(
                'fail' => extension_loaded('ionCube Loader'),
                'verbose' => 'off',
                'msgs' => array(
                    "The ionCube Loader extension could be incompatible with Phar files.",
                    "Anything prior to 4.0.9 will not work too well with Phar archives.",
                    "Consider upgrading to 4.0.9 or newer OR comment the 'ioncube_loader_lin_5.3.so' line from your 'php.ini'."
                )
            )
        );

        return $required;
    }

    public function getOptionals()
    {
        // sigchild
        ob_start();
        phpinfo(INFO_GENERAL);
        $phpinfo = ob_get_clean();
        preg_match('{Configure Command(?: *</td><td class="v">| *=> *)(.*?)(?:</td>|$)}m', $phpinfo, $config);

        $optionals = array(
            // APC
            'apc_cli disabled' => array(
                'fail' => ini_get('apc.enable_cli'),
                'msgs' => array(
                    "The apc.enable_cli should be set to Off.",
                    "Add the following to your 'php.ini':",
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
            ),

            // tidy
            'Tidy' => array(
                'fail' => !extension_loaded('tidy'),
                'msgs' => array(
                    "You may want to enable the Tidy extension.",
                )
            ),

            // PHP > 5.4
            'PHP version > 5.4' => array(
                'fail' => version_compare(PHP_VERSION, '5.4.0', '<'),
                'msgs' => array(
                    "PHP 5.4 introduces lots of nifty additions and is generally faster.",
                    "You should consider upgrading to PHP 5.4 or higher."
                )
            ),
        );

        return $optionals;
    }

    public function out($msg, $type=null)
    {

        $software_name = 'apix-server';
        $msg = str_replace("{software.name}", $software_name, $msg);

        switch ($type):
            case 'regex':
               parent::outRegex($msg);
            break;

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
