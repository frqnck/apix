<?php
namespace Apix;

class OutputJsonTest extends \PHPUnit_Framework_TestCase
{

    protected $json;

    public function setUp()
    {
        $this->json = new Output\Json;
    }

    protected function tearDown()
    {
        unset($this->json);
    }

    /**
     * @expectedException   \RuntimeException
     */
    public function testContentTypeIsNull()
    {
        $class = new \ReflectionClass($this->json);
        $prop = $class->getProperty('content_type');
        $prop->setAccessible(true);
        $ct = $prop->getValue($this->json);
        $this->assertEquals('application/json', $ct);
        
        $prop->setValue($this->json, null);
        $this->json->getContentType();
    }

    public function testEncode()
    {
        $data = array(1, 2);
        $json = $this->json->encode($data, 'r');

        $this->assertEquals('{"r":[1,2]}', $json);
    }

    /**
     * @depends php54
     */
    public function testEncodeWithPhp54()
    {
        $data = array(1, 2);
        $json = $this->json->encode($data, 'r');

        // TODO: test indent and coloring!
        $this->assertEquals('{"r":[1,2]}', $json);
    }

}