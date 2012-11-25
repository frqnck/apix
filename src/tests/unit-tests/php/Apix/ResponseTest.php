<?php

namespace Apix;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Apix\Response, Apix\Route
     */
    protected $response, $route;

    protected function setUp()
    {
        $request = HttpRequest::GetInstance();
        $this->response = new Response($request);
        $this->response->unit_test = true;

        $this->route = $this->getMock('Apix\Router');

        $this->route->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('/resource'));

        $this->route->expects($this->any())
            ->method('getController')
            ->will($this->returnValue('resource'));
    }

    protected function tearDown()
    {
        unset($this->response);
        unset($this->route);
    }

    /**
     * @covers Apix\Response::__construct
     */
    public function testConstructor()
    {
    }

    public function testGetSetFormat()
    {
        $this->response->setFormat('html', 'default');
        $this->assertSame('html', $this->response->getFormat() );

        $this->response->setFormat('XML', 'default');
        $this->assertSame('xml', $this->response->getFormat() );
    }

    /**
     * @expectedException           \DomainException
     * @expectedExceptionCode       406
     */
    public function testSetFormatThrowsDomainException()
    {
        $this->response->setFormat('whatever', 'default');
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
        $this->response->setHeader('Vary', '*');

        // check overide initial Vary header
        $this->response->setHeader('Vary', 'Accept');

        // check preserve previous Vary header
        $this->response->setHeader('Vary', 'Accept-Encoding', false);

        $this->response->setHeader('Vary', 'Accept-Encoding', false);
        $this->response->setHeader('X-HTTP-Method-Override', 'PUT');

        $this->assertSame('PUT', $this->response->getHeader('X-HTTP-Method-Override'));

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
        $this->assertSame('OK', Response::getStatusPrases(200));
        $this->assertSame('Unauthorized', Response::getStatusPrases(401));

        // long
        $this->assertSame('The request has succeeded.', Response::getStatusPrases(200, true));
        $this->assertSame('Not Authenticated.', Response::getStatusPrases(401, true));
    }

    public function testGetStatusAdjective()
    {
        $this->assertSame('successful', Response::getStatusAdjective(201));
        $this->assertSame('failed', Response::getStatusAdjective(401));
    }

    public function testCollate()
    {
        $this->assertSame(
            array('resource' => array('results')),
            $this->response->collate($this->route, array('results'))
        );
    }

    public function testCollateWithDebug()
    {
        $this->response->debug = true;
        $results = $this->response->collate($this->route, array('results'));
        $this->assertArrayHasKey('debug', $results);
        $this->assertArrayHasKey('timestamp', $results['debug']);
        #$this->assertSame('REQUEST_METHOD /', $results['debug']['request']);
        $this->assertSame(array('results'), $results['resource']);
    }

    public function testCollateWithSignature()
    {
        $this->response->sign = true;
        $results = $this->response->collate($this->route, array('results'));

        $this->assertArrayHasKey('signature', $results);
    }

    public function testCollateWithDebugAndSignature()
    {
        $this->response->debug=true;
        $this->response->sign=true;
        $results = $this->response->collate($this->route, array('results'));

        $this->assertArrayHasKey('resource', $results);
        $this->assertArrayHasKey('signature', $results);
        $this->assertArrayHasKey('debug', $results);
    }

    public function testGenerateAsLst()
    {
        $this->response->setFormats(array('lst'));
        $this->response->setFormat('lst');
        $results = array('results');

        $html = '<ul><li>root: <ul><li>resource: <ul><li>0: results</li></ul></li></ul></li></ul>';

        $this->assertSame(
            $html,
            $this->response->generate($this->route, $results)
        );
    }

    public function testGenerateAsJson()
    {
        $this->response->setFormat('json');
        $results = array('results');
        $this->assertSame(
            '{"root":{"resource":["results"]}}',
            $this->response->generate($this->route, $results)
        );
    }

    public function testGenerateAsXml()
    {
        $this->response->setFormat('xml');
        $results = array('results');

        $xml = '<root><resource><item>results</item></resource></root>' . PHP_EOL;

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . $xml,
            $this->response->generate($this->route, $results)
        );
    }

    public function testGenerateAsJsonp()
    {
        $this->markTestIncomplete(
            'TODO: This test has not been implemented yet.'
        );

        $this->response->setFormat('jsonp');
        $results = array('results');
        $this->assertSame(
            '{"root":{"resource":["results"]}}',
            $this->response->generate($this->route, $results)
        );
    }

}
