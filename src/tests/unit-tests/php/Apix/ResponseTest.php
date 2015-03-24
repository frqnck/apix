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

class ResponseTest extends TestCase
{

    /**
     * @var Apix\Response, Apix\Route
     */
    protected $response, $route;

    protected function setUp()
    {
        // Empty the plugins array from the main config.
        // $config = new Config::getInstance();
        // $config->set('plugins', array());

        $this->response = new Response(new HttpRequest());
        $this->response->unit_test = true;

        $this->route = $this->getMock('Apix\Router');

        $this->route->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('/resource'));

        $this->route->expects($this->any())
            ->method('getController')
            ->will($this->returnValue('resource'));

        $this->response->setRoute($this->route);
    }

    protected function tearDown()
    {
        unset($this->response, $this->route);
    }

    public function testGetRequest()
    {
        $this->assertInstanceOf('Apix\Request', $this->response->getRequest());
    }

    public function testGetRoute()
    {
        $this->assertInstanceOf('Apix\Router', $this->response->getRoute());
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

        $this->assertSame(
            'PUT',
            $this->response->getHeader('X-HTTP-Method-Override')
        );

        $this->assertSame(
            $headers,
            $this->response->getHeaders()
        );
    }

    public function testSendHeader()
    {
        $this->assertSame(
            array(404, 'org'),
            $this->response->sendHeader(404, 'org')
        );
    }

    public function testSendAllHttpHeaders()
    {
        $this->response->setHeader('Vary', 'Accept');
        $this->assertSame(
            array(array('X-Powered-By: org', true, 404), array('Vary: Accept')),
            $this->response->sendAllHttpHeaders(404, 'org')
        );
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
        $this->assertSame(
            'The request has succeeded.',
            Response::getStatusPrases(200, true)
        );
        $this->assertSame(
            'Not Authenticated.',
            Response::getStatusPrases(401, true)
        );
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
            $this->response->collate(array('results'))
        );
    }

    public function testGenerateAsHtml()
    {
        $this->response->setFormat('html');
        $this->response->generate(array('results'));
        $this->assertSame(
            '<ul><li>root: <ul><li>resource: '
            . '<ul><li>0: results</li></ul></li></ul></li></ul>',
            $this->response->getOutput()
        );
    }

    public function testGenerateAsJson()
    {
        $this->response->setFormat('json');
        $this->response->generate(array('results'));
        $this->assertSame(
            '{"root":{"resource":["results"]}}',
            $this->response->getOutput()
        );
    }

    public function testGenerateAsXml()
    {
        $this->response->setFormat('xml');
        $this->response->generate(array('results'));

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL
            .'<root><resource><item>results</item></resource></root>'.PHP_EOL,
            $this->response->getOutput()
        );
    }

    public function testGenerateAsJsonP()
    {
        $this->response->setFormats(array('jsonp'));
        $this->response->setFormat('jsonp');
        $this->response->generate(array('results'));
        $this->assertSame(
            'root({"root":{"resource":["results"]}});',
            $this->response->getOutput()
        );
    }

    public function testSetterAndGetterOutput()
    {
        $this->response->setOutput('foo');
        $this->assertEquals('foo', $this->response->getOutput());
    }

}
