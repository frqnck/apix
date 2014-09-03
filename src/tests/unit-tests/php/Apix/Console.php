<?php

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
