<?php
namespace Apix;

class OutputPhpTest extends \PHPUnit_Framework_TestCase
{

    protected $php;

    public function setUp()
    {
        $this->php = new Output\Php;
    }

    protected function tearDown()
    {
        unset($this->php);
    }

    public function testEncode()
    {
        $data = array(1, 2, 'abc');
        $php = $this->php->encode($data, 'apix');

        $r = array('apix'=>$data);
        $this->assertEquals(print_r($r, true), $php);
    }

}