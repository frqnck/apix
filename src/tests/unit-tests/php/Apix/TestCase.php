<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix;

// Makes sure the cache is off for all test.
$config = Config::getInstance();
$config->set('cache_annotation', false);

class TestCase extends \PHPUnit_Framework_TestCase
{

    public function skipIfMissing($name)
    {
        if (!extension_loaded($name)) {
            $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
            $extension = $prefix . "$name." . PHP_SHLIB_SUFFIX;
            if (
                !ini_get('enable_dl')
                || !( file_exists($extension) && dl($extension) )
            ) {
                self::markTestSkipped(
                    sprintf('The "%s" extension is required.', $name)
                );
            }
        }
    }

    // public function run(\PHPUnit_Framework_TestResult $result = null)
    // {
    //     $this->setPreserveGlobalState(false);
    //     return parent::run($result);
    // }
}
