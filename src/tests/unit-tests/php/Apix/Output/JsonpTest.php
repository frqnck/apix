<?php
namespace Apix;

class OutputJsonpTest extends \PHPUnit_Framework_TestCase
{

    protected $js;

    public function setUp()
    {
        $this->js = new Output\Jsonp;
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

}