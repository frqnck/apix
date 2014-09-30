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

class Console extends \PHPUnit_Framework_TestCase
{
    protected $cli;

    public function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testConstructor()
    {
        $cli = new Console(array('unit_test' => __FUNCTION__));
       # $this->assertEquals(__FUNCTION__, $config->get('unit_test'));
    }

}
