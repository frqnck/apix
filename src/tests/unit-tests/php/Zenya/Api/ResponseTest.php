<?php

namespace Zenya\Api;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zenya_Api_Response
     */
    protected $responce;

    protected function setUp()
    {
        $request = new Request();
        $this->response = new Response($request);
        $this->response->unit_test = true;
    }

    protected function tearDown()
    {
        unset($this->response);
    }

    /**
     * @covers Zenya\Api\Response::__construct
     */
    public function testConstructor()
    {
    }

    public function testGetSetFormat()
    {
        $this->response->setFormat('html');
        $this->assertSame('html', $this->response->getFormat() );
        
        $this->response->setFormat('XML');
        $this->assertSame('xml', $this->response->getFormat() );
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       406
     */
    public function testSetFormatThrowsException()
    {
        $this->response->setFormat('whatever');
    }

    public function testGetSetFormats()
    {
        $formats = array('json', 'xml', 'html');
        $this->response->setFormats($formats);

        $this->assertSame(
            $formats,
            $this->response->getFormats()
        );
    }

    public function testSetHeader()
    {
        $headers = array('Vary' => 'Accept', 'X-HTTP-Method-Override' => 'PUT');
        $this->response->setHeader('Vary', 'Accept');
        $this->response->setHeader('X-HTTP-Method-Override', 'PUT');
        $this->assertSame(
            $headers,
            $this->response->getHeaders()
        );
    }

    public function testSendHeader()
    {
        $this->assertSame(array(404, 'org'), $this->response->sendHeader(404, 'org'));
    }

    public function testSendAllHttpHeaders()
    {
        $this->response->setHeader('Vary', 'Accept');
        $this->assertSame(
            array(array('X-Powered-By: org', true, 404), array('Vary: Accept')),
            $this->response->sendAllHttpHeaders(404, 'org')
        );

        #$this->assertSame( $headers, headers_list() );
    }

    public function testGetSetHttpCode()
    {
        $this->assertSame(200, $this->response->getHttpCode());
        $this->response->setHttpCode(401);
        $this->assertSame(401, $this->response->getHttpCode());
    }

    public function testGetStatusPrases()
    {
        // short
        $this->assertSame('OK', $this->response->getStatusPrases());
        $this->assertSame('Unauthorized', $this->response->getStatusPrases(401));

        // long
        $this->assertSame('The request has succeeded.', $this->response->getStatusPrases(null, true));
        $this->assertSame('Not Authenticated.', $this->response->getStatusPrases(401, true));
    }

    public function testGetStatusAdjective()
    {
        $this->assertSame('successful', $this->response->getStatusAdjective());
        $this->assertSame('failed', $this->response->getStatusAdjective(401));
    }

    public function testCollate()
    {
        $this->assertSame(
            array('resource' => array('results')),
            $this->response->collate('resource', array('results'))
        );
    }

    public function testCollateWithDebug()
    {
        $this->response->debug = true;
        $this->assertSame(
            array(
                'resource' => array('results'),
                'debug' => array('headers'=> array(), 'format' => 'html')
            ),
            $this->response->collate('resource', array('results'))
        );
    }

    public function testCollateWithSignature()
    {
        $this->response->sign = true;
        $results = $this->response->collate('resource', array('results'));
        
        $this->assertArrayHasKey('signature', $results);
    }

    public function testCollateWithDebugAndSignature()
    {
        $this->response->debug=true;
        $this->response->sign=true;
        $results = $this->response->collate('resource', array('results'));

        $this->assertArrayHasKey('resource', $results);
        $this->assertArrayHasKey('signature', $results);
        $this->assertArrayHasKey('debug', $results);
    }

    public function testGenerateAsHtml()
    {
        $this->response->setFormat('html');
        $results = array('results');
        $this->assertSame(
            '<ul><li>root: <ul><li>resource: <ul><li>0: results</li></ul></li></ul></li></ul>',
            $this->response->generate('resource', $results)
        );
    }

    public function testGenerateAsJson()
    {
        $this->response->setFormat('json');
        $results = array('results');
        $this->assertSame(
            '{"root":{"resource":["results"]}}',
            $this->response->generate('resource', $results)
        );
    }

    public function testGenerateAsXml()
    {
        $this->response->setFormat('xml');
        $results = array('results');
        $this->assertSame('<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL . '<root><resource><item>results</item></resource></root>' . PHP_EOL,
            $this->response->generate('resource', $results)
        );
    }

}