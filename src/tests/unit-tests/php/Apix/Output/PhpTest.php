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

class PhpTest extends \PHPUnit_Framework_TestCase
{

    protected $php;

    public function setUp()
    {
        $this->data = array(1, 2, 'abc');
        $this->php = new Output\Php();
    }

    protected function tearDown()
    {
        unset($this->php);
    }

    public function testEncode()
    {
        $this->assertEquals(
            serialize($this->data),
            $this->php->encode($this->data)
        );
    }

    public function testEncodeWithRootNode()
    {
        $this->assertEquals(
            serialize(array('root'=>$this->data)),
            $this->php->encode($this->data, 'root')
        );
    }

    public function testEncodeWithDump()
    {
        $this->php->debug = true;
        $this->assertEquals(
            print_r($this->data, true),
            $this->php->encode($this->data)
        );
    }

}
