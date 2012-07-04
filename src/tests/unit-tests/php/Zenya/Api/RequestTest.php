<?php

namespace Zenya\Api;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zenya_Api_Request
     */
    protected $request = null;

    protected function setUp()
    {
        $this->request = Request::getInstance();
    }

    protected function tearDown()
    {
        unset($this->request);
    }

    public function testIsSingleton()
    {
        $req = Request::getInstance();
        $req2 = Request::getInstance();

        $this->assertSame($req, $req2);
    }
    /**
     * @covers Zenya\Api\Request::__clone
     */
    public function Off___testIsSingletonIsNotClonable()
    {
        // $r = clone $this->request;
        $r = new \ReflectionClass($this->request);
        $p = $r->getMethods(\ReflectionMethod::IS_PRIVATE|\ReflectionMethod::IS_FINAL);
        $this->assertSame('__clone', $p[0]->name);
    }

    public function testGetSetUri()
    {
        $this->assertSame('/', $this->request->getUri() );
        $this->request->setUri('/qwerty/');
        $this->assertSame('/qwerty', $this->request->getUri() );
    }

    /**
     * @dataProvider headersProvider
    */
    public function testGetUriWithHttpHeaders($str)
    {
        $_SERVER[$str] = "/$str";
        $this->request->setUri();
        $this->assertSame($_SERVER[$str], $this->request->getUri());
        $_SERVER[$str] = null;
    }

    public function headersProvider()
    {
        return array(
            array('HTTP_X_REWRITE_URL'),
            array('REQUEST_URI'),
            array('PATH_INFO'),
            array('ORIG_PATH_INFO')
        );
    }

    public function testGetUriWithIIS_WasUrlRewritten()
    {
        $_SERVER['IIS_WasUrlRewritten'] = '1';
        $_SERVER['UNENCODED_URL'] = '/IIS_WasUrlRewritten';
        $this->request->setUri();
        $this->assertSame('/IIS_WasUrlRewritten', $this->request->getUri());
        $_SERVER['UNENCODED_URL'] = null;
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

        $this->request->setParams();
        $this->assertSame($_REQUEST, $this->request->getParams() );
    }

    public function testGetSetMethod()
    {
        $this->assertSame('GET', $this->request->getMethod() );

        $_SERVER['REQUEST_METHOD'] = 'REQUEST_METHOD';
        $this->request->setMethod();
        $this->assertSame('REQUEST_METHOD', $this->request->getMethod() );

        $this->request->setParam('_method', 'qs');
        $this->request->setMethod();
        $this->assertSame('QS', $this->request->getMethod() );

        $this->request->setHeader('X-HTTP-Method-Override', 'head');
        $this->request->setMethod();
        $this->assertSame('HEAD', $this->request->getMethod() );

        $this->request->setMethod('methd');
        $this->assertSame('METHD', $this->request->getMethod(), 'Should go all uppercase');
    }

    public function testGetSetHeaders()
    {
        $this->request->setHeaders(array('a', 'b', 'c'));
        $this->assertSame(array('a', 'b', 'c'), $this->request->getHeaders() );

        $this->request->setHeaders();
        $this->assertSame($_SERVER, $this->request->getHeaders() );

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

    public function testSetBodyFromStream()
    {
        $this->request->setBody();
        $this->assertSame('', $this->request->getBody());

        $this->request->setBodyStream(APP_TESTDIR . '/Zenya/Api/Fixtures/body.txt');

        $this->request->setBody();

        $this->assertSame('body1=value1&body2=value2', $this->request->getBody());
    }

    public function testHasBody()
    {
        $this->request->setBody('');
        $this->assertSame('', $this->request->getBody());

        $this->assertFalse($this->request->hasBody());

        $this->request->setBody('body-data');

        $this->assertTrue($this->request->hasBody());

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
        $this->assertSame($this->data, $this->request->getBody(false));
        $this->assertSame($raw, $this->request->getRawBody());
    }

    public function testGetSetBodyGzip()
    {
        $raw = gzencode($this->data);
        $this->request->setHeader('content-encoding', 'gzip');
        $this->request->setBody($raw);
        $this->assertSame($this->data, $this->request->getBody(false));
        $this->assertSame($raw, $this->request->getRawBody());
    }

    public function testGetSetBody()
    {
        $raw = $this->data;
        $this->request->setHeader('content-encoding', 'hashed');
        $this->request->setBody($raw);
        $this->assertSame($this->data, $this->request->getBody(false));
        $this->assertSame($raw, $this->request->getRawBody());
    }

    public function testGetSetBodyCache()
    {
        $raw = gzencode($this->data);
        $this->request->setBody($raw);
        $this->assertSame($this->data, $this->request->getBody());
        $this->assertSame($raw, $this->request->getRawBody());
    }

}
