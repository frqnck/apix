<?php

namespace Zenya\Api;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zenya_Api_Request
     */
    protected $request;

    protected function setUp()
    {
        $this->request = new Request;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->request);
    }

    /**
     * @covers Zenya\Api\Server::__construct
     */
    public function testConstructor()
    {
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    public function testGetSetUri()
    {
        $this->request->setUri(null);
        $this->assertSame('/', $this->request->getUri() );
        $this->request->setUri('/qwerty/');
        $this->assertSame('/qwerty', $this->request->getUri() );
        // todo: $_SERVER['HTTP_X_REWRITE_URL']

        $this->request->setHeader('X-HTTP-Method-Override', 'head');

    }

    public function testGetSetParam()
    {
        $this->request->setParam('hello', 'world');
        $this->assertEquals('world', $this->request->getParam('hello') );
    }

    public function testGetSetParamWithFilters()
    {
        // alnum, alpha, digit
        $this->request->setParam('arg', '#azerty+&%$1-23.4567');
        $this->assertSame('azerty1234567', $this->request->getParam('arg', 'alnum') );
        $this->assertSame('azerty', $this->request->getParam('arg', 'alpha') );
        $this->assertSame('1234567', $this->request->getParam('arg', 'digit') );
    }

    public function testGetSetParams()
    {
        $this->request->setParams(array('a', 'b', 'c'));
        $this->assertSame(array('a', 'b', 'c'), $this->request->getParams() );
    }

    public function testGetSetMethod()
    {
        $this->request->setMethod(null);
        $this->assertSame('GET', $this->request->getMethod() );

        $_SERVER['REQUEST_METHOD'] = 'REQUEST_METHOD';
        $this->request->setMethod(null);
        $this->assertSame('REQUEST_METHOD', $this->request->getMethod() );

        $this->request->setParam('_method', 'qs');
        $this->request->setMethod(null);
        $this->assertSame('QS', $this->request->getMethod() );

        $this->request->setHeader('X-HTTP-Method-Override', 'head');
        $this->request->setMethod(null);
        $this->assertSame('HEAD', $this->request->getMethod() );

        $this->request->setMethod('methd');
        $this->assertSame('METHD', $this->request->getMethod() );
    }

    public function testGetSetHeaders()
    {
        $this->request->setHeaders(array('a', 'b', 'c'));
        $this->assertSame(array('a', 'b', 'c'), $this->request->getHeaders() );
    }

    public function testGetIp()
    {
        $this->request->setHeader('REMOTE_ADDR', '1.');
        $this->assertSame('1.', $this->request->getIp() );

        $this->request->setHeader('HTTP_X_FORWARDED_FOR', '2.');
        $this->assertSame('2.', $this->request->getIp() );

        $this->request->setHeader('HTTP_CLIENT_IP', '3.');
        $this->assertSame('3.', $this->request->getIp() );
    }

        protected $data = <<<DATA
// RFC 2616 defines 'deflate' encoding as zlib format from RFC 1950,
// while many applications send raw deflate stream from RFC 1951.
// We should check for presence of zlib header and use gzuncompress() or
// gzinflate() as needed. See bug #15305
DATA;

    public function testGetSetBodyDeflate()
    {
        $raw = gzdeflate($this->data);

        $this->request->setHeader('content-encoding', 'deflate');
        $this->request->setBody($raw);
        $this->assertSame($this->data, $this->request->getBody());
        $this->assertSame($raw, $this->request->getRawBody());
    }

    public function testGetSetBodyGzip()
    {
        $raw = gzencode($this->data);
        $this->request->setHeader('content-encoding', 'gzip');
        $this->request->setBody($raw);
        $this->assertSame($this->data, $this->request->getBody());
        $this->assertSame($raw, $this->request->getRawBody());
    }

    public function testGetSetBody()
    {
        $raw = $this->data;
        $this->request->setHeader('content-encoding', 'hashed');
        $this->request->setBody($raw);
        $this->assertSame($this->data, $this->request->getBody());
        $this->assertSame($raw, $this->request->getRawBody());
    }

}