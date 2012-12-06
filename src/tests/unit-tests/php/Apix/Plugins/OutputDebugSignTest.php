<?php
namespace Apix\Plugins;

use Apix\HttpRequest,
    Apix\Response,
    Apix\Config,
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

    public function testDebugIsDisable()
    {
        $d = new OutputDebug( array('enable' => false) );
        $this->assertFalse( $d->update($this->response) );
    }

    public function testOutpuDebug()
    {
        if(!defined('APIX_START_TIME')) {
            define('APIX_START_TIME', 0);
        }
        $_SERVER['X_AUTH_USER'] = 'UNIT-TEST-USER';
        $_SERVER['X_AUTH_KEY'] = 'UNIT-TEST-KEY';

        $d = new OutputDebug;
        $d->update($this->response);

        $r =  $this->response->results;

        $this->assertArrayHasKey('debug', $r);

        // $this->assertSame(array(), $r['debug']);
        $this->assertArrayHasKey('timestamp', $r['debug']);
        $this->assertArrayHasKey('request', $r['debug']);

        $this->assertArrayHasKey('timing', $r['debug']);

        $this->assertArrayHasKey('headers', $r['debug']);
        $this->assertArrayHasKey('X_AUTH_USER', $r['debug']['headers']);
        $this->assertArrayHasKey('X_AUTH_KEY', $r['debug']['headers']);
    }

    public function testOutpuSign()
    {
        $d = new OutputSign;
        $d->update($this->response);

        $r =  $this->response->results;

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

}