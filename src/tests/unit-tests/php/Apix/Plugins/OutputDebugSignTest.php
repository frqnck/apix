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
    Apix\TestCase;

class OutputDebugSignTest extends TestCase
{

    protected $response;

    public function setUp()
    {
        $this->response = new Response(
            HttpRequest::GetInstance()
        );
        $this->response->unit_test = true;

        $route = $this->getMock('Apix\Router');

        $route->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('/resource'));

        $route->expects($this->any())
            ->method('getController')
            ->will($this->returnValue('resource'));

        $this->response->setRoute($route);
    }

    protected function tearDown()
    {
        unset($this->response);
    }

    public function testSignatureIsDisable()
    {
        $d = new OutputSign( array('enable' => false) );
        $this->assertFalse( $d->update($this->response) );
    }

    public function testOutpuSign()
    {
        $d = new OutputSign( array('enable' => true) );
        $d->update($this->response);

        $r = $this->response->results;

        $this->assertArrayHasKey('signature', $r);
        $this->assertSame(
            array(
                'resource'  => ' /resource',
                'status'    => '200 OK - successful',
                'client_ip' => null
            ),
            $r['signature']
        );
    }

    public function testSignExtras()
    {
        $d = new OutputSign( array('enable' => true, 'extras'=>'string') );
        $d->update($this->response);
        $r = $this->response->results;

        $this->assertSame('string', $r['signature']['extras'] );
    }

    public function testSignDoesAppend()
    {
        $this->response->results = array('bar'=>'foo', 'foo'=>'bar');

        $d = new OutputSign( array('enable' => true, 'prepend'=>false) );
        $d->update($this->response);
        $r = $this->response->results;

        $this->assertSame(2, array_search('signature', array_keys($r)));
    }

    public function testSignDoesPrepend()
    {
        $this->response->results = array('bar'=>'foo', 'foo'=>'bar');

        $d = new OutputSign( array('enable' => true, 'prepend'=>true) );
        $d->update($this->response);
        $r = $this->response->results;

        $this->assertSame(0, array_search('signature', array_keys($r)));
    }

    public function testDebugIsDisable()
    {
        $d = new OutputDebug( array('enable' => false) );
        $this->assertFalse( $d->update($this->response) );
    }

    public function testOutpuDebug()
    {
        if (!defined('APIX_START_TIME')) {
            define('APIX_START_TIME', 0);
        }
        $_SERVER['X_AUTH_USER'] = 'UNIT-TEST-USER';
        $_SERVER['X_AUTH_KEY'] = 'UNIT-TEST-KEY';

        $d = new OutputDebug( array('enable' => true) );
        $d->update($this->response);

        $r = $this->response->results;

        $this->assertArrayHasKey('debug', $r);

        // $this->assertSame(array(), $r['debug']);
        $this->assertArrayHasKey('timestamp', $r['debug']);
        $this->assertArrayHasKey('request', $r['debug']);

        $this->assertArrayHasKey('timing', $r['debug']);

        $this->assertArrayHasKey('headers', $r['debug']);
        $this->assertArrayHasKey('X_AUTH_USER', $r['debug']['headers']);
        $this->assertArrayHasKey('X_AUTH_KEY', $r['debug']['headers']);
    }

    public function testDebugExtras()
    {
        $d = new OutputDebug( array('enable' => true, 'extras'=>'string') );
        $d->update($this->response);
        $r = $this->response->results;

        $this->assertSame('string', $r['debug']['extras'] );
    }

    public function testDebugDoesAppend()
    {
        $this->response->results = array('bar'=>'foo', 'foo'=>'bar');

        $d = new OutputDebug( array('enable' => true, 'prepend'=>false) );
        $d->update($this->response);
        $r = $this->response->results;

        $this->assertSame(2, array_search('debug', array_keys($r)));
    }

    public function testDebugDoesPrepend()
    {
        $this->response->results = array('bar'=>'foo', 'foo'=>'bar');

        $d = new OutputDebug( array('enable' => true, 'prepend'=>true) );
        $d->update($this->response);
        $r = $this->response->results;

        $this->assertSame(0, array_search('debug', array_keys($r)));
    }

    public function bytesProvider()
    {
        return array(
          array('1 B', 1),
          array('1 kB', 1024),
          array('1 MB', 1024*1024),
          array('1 GB', 1024*1024*1024),
          array('1 TB', 1024*1024*1024*1024),
          array('1.49 kB', 1525),
          array('14.89 kilobytes', 15250, true)
        );
    }

    /**
     * @dataProvider bytesProvider
     */
    public function testFormatBytesToString($expected, $bytes, $long=false)
    {
        $d = new OutputDebug( array('enable' => true, 'prepend' => true) );

        $this->assertSame(
            $expected,
            OutputDebug::formatBytesToString($bytes, $long)
        );
    }

}
