<?php
namespace Apix\Plugin;

use Apix\HttpRequest,
    Apix\Response,
    Apix\TestCase;

class CorsTest extends TestCase
{

    protected $plugin, $response;

    public function setUp()
    {
        $this->request = HttpRequest::GetInstance();

        $this->response = new Response(
            HttpRequest::GetInstance()
        );
        $this->response->unit_test = true;

        $this->route = $this->getMock('Apix\Router');

        $this->route->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('/resource'));

        $this->route->expects($this->any())
            ->method('getController')
            ->will($this->returnValue('resource'));

        $this->response->setRoute($this->route);

        $options = array(
            'enable'    => true,
            'generic'   => array(
                'indent'        => true,
                'indent-spaces' => 2,
                'show-body-only' => true
            )
        );

        $this->plugin = new Cors($options);
    }

    protected function tearDown()
    {
        unset($this->plugin, $this->response, $this->route);
    }

    public function testIsDisable()
    {
        $plugin = new Cors( array('enable' => false) );
        $this->assertFalse( $plugin->update($this->response) );
    }

    /**
     * @dataProvider originsProvider
     */
    public function testIsOriginAllowed($origin, $host, $result, $port='(:[0-9]+)?', $scheme='https?')
    {
        $this->assertSame(
            $result,
            $this->plugin->isOriginAllowed($origin, $host, $port, $scheme)
        );
    }

    public function OriginsProvider()
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

    public function TODOtestHearder()
    {
        $this->plugin->update($this->response);

        var_dump( $this->response->getHeaders() );

        var_dump( $this->response->getHeaders() );

        $this->assertSame(
            array(),
            $this->response->getHeaders()
        );
    }

}
