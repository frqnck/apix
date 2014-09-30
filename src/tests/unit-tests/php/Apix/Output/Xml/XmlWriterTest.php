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

require_once APP_TESTDIR . '/Apix/Output/XmlTest.php';

class XmlWriterTest extends XmlTest
{

    public function setUp()
    {
        if (!extension_loaded('tidy')) {
            $this->markTestSkipped(
              'The XmlWriter extension is not available.'
            );
        }

        $this->xml = new Output\Xml\XmlWriter();
    }

    public function testSimpleArray()
    {
        $data = array(1, 2, 'abc');
        $xml = $this->xml->encode($data, 'r');

        $this->assertXml(
            '<r><item>1</item><item>2</item><item>abc</item></r>',
            $xml
        );
    }

    public function testComplexArray()
    {
        $data = array(1, array(2,'abc'));
        $xml = $this->xml->encode($data, 'r');

        $this->assertXml(
            '<r><item>1</item><items><item>2</item><item>abc</item></items></r>',
            $xml
        );
    }

    public function testAssociativeArray()
    {
        $data = array(1, 'myKey'=>array(2,'abc'));
        $xml = $this->xml->encode($data, 'r');

        $this->assertXml(
            '<r><item>1</item><myKey><item>2</item><item>abc</item></myKey></r>',
            $xml
        );
    }

    public function testNullValue()
    {
        $this->assertXml(
            '<r><null/></r>',
            $this->xml->encode(array('null'=>null), 'r')
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
     *       XmlWriter: double-quotes are converted (wrongly &quot;).
     *       SimpleXml: double-quotes are NOT converted
     *
     * @see https://bugs.php.net/bug.php?id=63589
     */
    public function testSpecialCharsQuotes()
    {
        $this->markTestSkipped("XmlWriter should not convert double-quotes. Bug #63589");
        $this->assertXml(
            '<r><item>\'"</item></r>',
            $this->xml->encode(array('\'"'), 'r')
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
