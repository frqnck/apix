<?php
namespace Apix;

require_once APP_TESTDIR . '/Apix/Output/XmlTest.php';

class SimpleXmlTest extends OutputXmlTest
{

    public function setUp()
    {
        if (!extension_loaded('SimpleXML')) {
            $this->markTestSkipped(
              'The SimpleXML extension is not available.'
            );
        }

        $this->xml = new Output\Xml\SimpleXml;
    }

    public function testSimpleArray()
    {
        $xml = $this->xml->encode(array(1, 2, 'abc'), 'r');

        $this->assertXml(
            '<r><item>1</item><item>2</item><item>abc</item></r>',
            $xml
        );
    }

    public function testComplexArray()
    {
        $this->assertXml(
            '<r><item>1</item><items><item>2</item><item>abc</item></items></r>',
            $this->xml->encode(array(1, array(2,'abc')), 'r')
        );
    }

    public function testAssociativeArray()
    {
        $this->assertXml(
            '<r><item>1</item><myKey><item>2</item><item>abc</item></myKey></r>',
            $this->xml->encode(array(1, 'myKey'=>array(2,'abc')), 'r')
        );
    }

    public function testNullValue()
    {
        $this->assertXml(
            '<r><null/></r>',
            $this->xml->encode(array('null'=>null), 'r')
        );
    }

    public function testTheSpecialCharsMethodItself()
    {
        $this->assertEquals(
            '&amp;&quot;\'&lt;&gt; ?|\-_+=@£$€*/&quot;:;[]{}',
            $this->xml->specialChars('&"\'<> ?|\\-_+=@£$€*/":;[]{}')
        );
    }

    public function testSpecialChars()
    {
        $this->assertXml(
            '<r><item>&amp;&lt;&gt; ?|\-_+=@£$€*/:;[]{}</item></r>',
            $this->xml->encode(array('&<> ?|\\-_+=@£$€*/:;[]{}'), 'r')
        );
    }

    /**
     * @todo Quotes handling is different between XmlWriter and SimpleXML
     *       XmlWriter: double-quotes are converted.
     *       SimpleXml: double-quotes are NOT converted
     */
    public function testSpecialCharsQuotes()
    {
        $this->assertXml(
            '<r><item>"\'"</item></r>',
            $this->xml->encode(array('"\'"'), 'r')
        );
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