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

namespace Apix\Plugin;

use Apix\HttpRequest,
    Apix\Response,
    Apix\TestCase,
    Apix\Service;

class CorsTest extends TestCase
{

    protected $plugin, $request, $response, $opts;

    public function setUp()
    {
        $_SERVER['HTTP_ORIGIN'] = 'https://foo.bar';

        $this->request = new HttpRequest();
        $this->response = new Response($this->request);
        $this->response->unit_test = true;

        Service::set('response', $this->response);

        $this->entity = $this->getMock('Apix\Entity');

        $this->plugin = new Cors(
            array('enable' => true, 'host' => 'foo\.bar')
        );

        $this->opts = $this->plugin->getOptions();
    }

    protected function tearDown()
    {
        unset($this->plugin, $this->request, $this->response, $this->opts);
    }

    public function testIsDisable()
    {
        $this->plugin = new Cors( array('enable' => false) );
        $this->assertFalse( $this->plugin->update( $this->entity ) );
    }

    protected function updatePlugin()
    {
        $this->plugin = new Cors( $this->opts );
        $this->assertTrue( $this->plugin->update( $this->entity ) );
        // return $plugin;
    }

    /**
     * @expectedException           \DomainException
     * @expectedExceptionCode       403
     */
    public function testNotAllowedThrowsDomainException()
    {
        $_SERVER['HTTP_ORIGIN'] = null;
        $this->updatePlugin();
    }

    public function testAllowOriginIsOrigin()
    {
        $this->opts['allow-origin'] = 'origin';
        $this->updatePlugin();
        $this->assertSame(
            $_SERVER['HTTP_ORIGIN'],
            $this->response->getHeader('Access-Control-Allow-Origin')
        );
    }

    public function testAllowOriginIsStringList()
    {
        $this->opts['allow-origin'] = 'foo,bar';
        $this->updatePlugin();
        $this->assertSame(
            $this->opts['allow-origin'],
            $this->response->getHeader('Access-Control-Allow-Origin')
        );
    }

    public function testAllowOriginIsNull()
    {
        $this->opts['allow-origin'] = 'null';
        $this->updatePlugin();
        $this->assertSame(
            $this->opts['allow-origin'],
            $this->response->getHeader('Access-Control-Allow-Origin')
        );
    }

    public function testAllowCredentials()
    {
        $this->opts['allow-credentials'] = true;
        $this->updatePlugin();
        $this->assertTrue(
            $this->response->getHeader('Access-Control-Allow-Credentials')
        );
    }

    public function testAllowCredentialsSetToFalse()
    {
        $this->opts['allow-credentials'] = false;
        $this->updatePlugin();
        $this->assertNull(
            $this->response->getHeader('Access-Control-Allow-Credentials')
        );
    }

    public function testExposeHeadersIsNull()
    {
        $this->opts['expose-headers'] = null;
        $this->updatePlugin();
        $this->assertNull(
            $this->response->getHeader('Access-Control-Expose-Headers')
        );
    }

    public function testExposeHeadersIsSet()
    {
        $this->opts['expose-headers'] = 'x-foo-bar';
        $this->updatePlugin();
        $this->assertSame(
            $this->opts['expose-headers'],
            $this->response->getHeader('Access-Control-Expose-Headers')
        );
    }

    /**
     * @dataProvider originsProvider
     */
    public function testIsOriginAllowed(
        $origin, $host, $result, $port = '(:[0-9]+)?', $scheme = 'https?'
    ) {
        $this->assertSame(
            $result,
            Cors::isOriginAllowed($origin, $host, $port, $scheme)
        );
    }

    public function originsProvider()
    {
        return array(
            array(
                'origin'    => 'http://foo.bar',
                'host'      => 'foo.bar',
                'result'    => true,
            ),
            array(
                'origin'    => 'https://foo.bar',
                'host'      => 'foo.bar',
                'result'    => true
            ),
            array(
                'origin'    => 'http://foo.bar:80',
                'host'      => 'foo.bar',
                'result'    => true,
            ),
            array(
                'origin'    => 'http://fuzz.bar',
                'host'      => 'foo.bar',
                'result'    => false,
            ),
            array(
                'origin'    => 'http://fox-foo.bar',
                'host'      => 'fox\.foo\.bar',
                'result'    => false,
            ),
            array(
                'origin'    => 'http://fox.foo.bar',
                'host'      => 'fox.foo.bar',
                'result'    => true,
            ),
            array(
                'origin'    => 'http://more.any.foo.bar',
                'host'      => '.*\.foo\.bar',
                'result'    => true,
            ),
            array(
                'origin'    => 'http://foo.bar:88',
                'host'      => 'foo\.bar',
                'result'    => false,
                'port'      => ':80'
            ),
            array(
                'origin'    => 'http://foo.bar:88',
                'host'      => 'foo\.bar',
                'result'    => true,
                'port'      => ':88'
            ),
            array(
                'origin'    => 'https://foo.bar:88',
                'host'      => 'foo\.bar',
                'result'    => true,
                'port'      => ':88',
                'scheme'    => 'https'
            ),
            array(
                'origin'    => 'http://1.2.3.5',
                'host'      => '1\.2\.3\.4|1\.2\.3\.5',
                'result'    => true
            ),
            array(
                'origin'    => 'http://apix.dev2.info.com',
                'host'      => '.*\.info\.com',
                'result'    => true
            ),
        );
    }

    public function testIsPreflightIsFalse()
    {
        $this->request->setMethod('GET');
        $this->assertFalse( Cors::IsPreflight($this->request) );

        $this->request->setMethod('HEAD');
        $this->assertFalse( Cors::IsPreflight($this->request) );

        $this->request->setHeader('CONTENT_TYPE', 'text/plain');
        $this->request->setMethod('POST');
        $this->assertFalse( Cors::IsPreflight($this->request) );

        $this->assertNull(
            $this->response->getHeader('Access-Control-Max-Age')
        );
    }

    public function testIsPreflightIsTrue()
    {
        $this->request->setMethod('OPTIONS');
        $this->assertTrue( Cors::IsPreflight($this->request) );

        $this->request->setHeader('CONTENT_TYPE', 'text/xml');
        $this->request->setMethod('POST');
        $this->assertTrue( Cors::IsPreflight($this->request) );
    }

    public function testIfPreflightDoesSetAccessControlMaxAge()
    {
        $this->request->setMethod('OPTIONS');
        $this->updatePlugin();
        $this->assertEquals(
            $this->opts['max-age'],
            $this->response->getHeader('Access-Control-Max-Age')
        );
    }

    public function testIfPreflightWithRequestMethod()
    {
        $this->opts['allow-methods'] = 'FOO, BAR';

        $this->request->setMethod('OPTIONS');
        $this->request->setHeader('Access-Control-Request-Method', 'BAR');
        $this->assertTrue( Cors::IsPreflight($this->request) );

        $this->updatePlugin();

        $this->assertEquals(
            $this->opts['allow-methods'],
            $this->response->getHeader('Access-Control-Allow-Methods')
        );
    }

    /**
     * @expectedException           \DomainException
     * @expectedExceptionCode       403
     */
    public function testIfPreflightWithWrongRequestMethod()
    {
        $this->opts['allow-methods'] = 'FOO, BAR';

        $this->request->setMethod('OPTIONS');
        $this->request->setHeader('Access-Control-Request-Method', 'PUT');
        $this->assertTrue( Cors::IsPreflight($this->request) );

        $this->updatePlugin();
    }

    public function testIfPreflightWithRequestHeaders()
    {
        $this->opts['allow-headers'] = 'x-foo-bar,x-booz-bar';

        $this->request->setHeader(
            'Access-Control-Request-Headers',
            'x-booz-bar'
        );
        $this->assertTrue( Cors::IsPreflight($this->request) );

        $this->updatePlugin();

        $this->assertEquals(
            $this->opts['allow-headers'],
            $this->response->getHeader('Access-Control-Allow-Headers')
        );
    }

    /**
     * @expectedException           \DomainException
     * @expectedExceptionCode       403
     */
    public function testIfPreflightWithWrongRequestHeaders()
    {
        $this->opts['allow-headers'] = 'x-foo-bar,x-booz-bar';

        $this->request->setHeader('Access-Control-Request-Headers', 'x-null');
        $this->assertTrue( Cors::IsPreflight($this->request) );

        $this->updatePlugin();
    }

}
