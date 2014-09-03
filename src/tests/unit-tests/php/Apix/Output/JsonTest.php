<?php
namespace Apix;

class JsonTest extends \PHPUnit_Framework_TestCase
{

    protected $json;

    public function setUp()
    {
        $this->json = new Output\Json();
    }

    protected function tearDown()
    {
        unset($this->json);
    }

    public function testContentIsSet()
    {
        $this->assertEquals(
            'application/json',
            $this->json->getContentType()
        );
    }

    /**
     * @expectedException   \RuntimeException
     */
    public function testContentTypeIsNull()
    {
        $ref = new \ReflectionClass($this->json);
        $prop = $ref->getProperty('content_type');
        $prop->setAccessible(true);
        $prop->setValue($this->json, null);
        $this->json->getContentType();
    }

    public function testEncode()
    {
        $data = array(1, 2);
        $json = $this->json->encode($data, 'r');

        $this->assertEquals('{"r":[1,2]}', $json);
    }

    public function testEncodeAsPerRfc4627()
    {
        $data = array('<>\'&"');
        $json = $this->json->encode($data, 'r');

        $this->assertEquals('{"r":["\u003C\u003E\u0027\u0026\u0022"]}', $json);
    }

    /**
     * TODO: SKIPPED for now!!!
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
