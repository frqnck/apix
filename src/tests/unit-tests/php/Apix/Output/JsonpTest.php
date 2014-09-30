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

class JsonpTest extends \PHPUnit_Framework_TestCase
{

    protected $js;

    public function setUp()
    {
        $this->js = new Output\Jsonp();
    }

    protected function tearDown()
    {
        unset($this->js);
    }

    public function testEncode()
    {
        $data = array(1, 2, 'abc');
        $js = $this->js->encode($data, 'apix');

        $this->assertEquals('apix({"apix":[1,2,"abc"]});', $js);
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testThrowsInvalidArgumentUsingReservedJsWord()
    {
        $this->js->encode(array(), 'debugger');
    }

    /**
     * @expectedException   \InvalidArgumentException
     */
    public function testThrowsInvalidArgumentSyntaxInvalidJs()
    {
        $this->js->encode(array(), '\1');
    }

}
