<?php
namespace Apix;

class OutputXmlTest extends \PHPUnit_Framework_TestCase
{

    protected $xml;

    public function setUp()
    {
        $this->xml = new Output\Xml;
    }

    protected function tearDown()
    {
        unset($this->xml);
    }

    public function assertXml($str, $xml)
    {
        $this->assertEquals(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"
            . PHP_EOL . $str . PHP_EOL,
            $xml
        );
    }

    public function testContentIsSet()
    {
        $this->assertEquals(
            'text/xml',
            $this->xml->getContentType()
        );
    }

    /**
     * @expectedException   \RuntimeException
     */
    public function testContentTypeIsNull()
    {
        $ref = new \ReflectionClass($this->xml);
        $prop = $ref->getProperty('content_type');
        $prop->setAccessible(true);
        $prop->setValue($this->xml, null);
        $this->xml->getContentType();
    }

    public function testBooleanString()
    {
        $this->assertEquals(
            'True', $this->xml->booleanString(true)
        );
        $this->assertEquals(
            'False', $this->xml->booleanString(false)
        );
        $this->assertEquals(
            0, $this->xml->booleanString(0)
        );
        $this->assertEquals(
            1, $this->xml->booleanString(1)
        );
        $this->assertEquals(
            -1, $this->xml->booleanString(-1)
        );
    }

}
