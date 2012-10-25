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

    public function testEncode()
    {
        $data = array(1, 2);
        $xml = $this->xml->encode($data, 'r');

        $this->assertEquals("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<r>\n  <item>1</item>\n  <item>2</item>\n</r>", $xml);
    }

    /**
     * @covers Apix\Output\Xml::arrayToXml
     */
    public function testEncodeAttributes()
    {
        $this->markTestIncomplete('TODO: testEncodeAttributes');

        // todo
        $data = array('@attributes'=>'vattributes');
        $xml = $this->xml->encode($data, 'r');
        print_r($xml);exit;
    }

}
