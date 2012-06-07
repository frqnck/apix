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
        $server = null;
        $this->response = new Response();
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
        $this->response->unit_test = true;
        $this->assertSame(array(404, 'org'), $this->response->sendHeader(404, 'org'));
    }

    public function testSendAllHttpHeaders()
    {
        $this->response->unit_test = true;
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
        $this->assertSame('200 OK', $this->response->getStatusPrases());
        $this->assertSame('401 Unauthorized', $this->response->getStatusPrases(401));

        // long
        $this->assertSame('The request has succeeded.', $this->response->getStatusPrases(null, true));
        $this->assertSame('Not Authenticated.', $this->response->getStatusPrases(401, true));
    }

    public function OfftestToArray()
    {
        $this->response->toArray();
    }

}