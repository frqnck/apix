<?php

namespace Apix;

use Apix\TestCase;

class MainTest extends TestCase
{

    protected $server, $request;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Apix\HttpRequest')
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->server = new Server(null, $this->request);
    }

    protected function tearDown()
    {
        unset($this->server);
        unset($this->request);
    }

    /**
     * @expectedException           \DomainException
     * @expectedExceptionCode       406
     */
    public function testNegotiateFormatUseDefaultAndthrowsDomainException()
    {
        $c = array(
            'default_format'    => 'defaultExt',
            'controller_ext'    => false,
            'format_override'   => false,
            'http_accept'       => false,
        );
        $format = $this->server->negotiateFormat($c);
        $this->assertEquals(
            'defaultExt',
            $format,
            "Expect default ext when the negotation chain elements are false"
        );
    }

    public function negotiateFormatProvider()
    {
        return array(
            'controller_ext set to true' => array(
                'uri'=>'/index.php/api/v1/mock.xml/test/param',
                'options' => array(
                    'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
                    'default_format'    => 'xml',
                    'controller_ext'    => true,
                    'format_override'   => false,
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/mock/test/param',
                    'format' => 'xml',
                )
            ),
            'controller_ext set to false' => array(
                'uri'=>'/index.php/api/v1/mock.json/test/param',
                'options' => array(
                    'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
                    'default_format'    => 'json',
                    'controller_ext'    => false,
                    'format_override'   => false,
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/mock.json/test/param',
                    'format' => 'json',
                )
            ),
            'format_override set' => array(
                'uri'=>'/index.php/api/v1/mock.json/test/param',
                'options' => array(
                    'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
                    'default_format'    => 'json',
                    'controller_ext'    => false,
                    'format_override'   => 'html',
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/mock.json/test/param',
                    'format' => 'html',
                )
            ),
            'http_accept is set (but none provided)' => array(
                'uri'=>'/index.php/api/v1/mock.json/test/param',
                'options' => array(
                    'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
                    'default_format'    => 'xml',
                    'controller_ext'    => false,
                    'format_override'   => false,
                    'http_accept'       => true,
                ),
               'expected' => array(
                    'path' => '/mock.json/test/param',
                    'format' => 'xml',
                )
            ),
            'all false, should use default' => array(
                'uri'=>'/index.php/api/v1/mock.json/test/param',
                'options' => array(
                    'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
                    'default_format'    => 'xml',
                    'controller_ext'    => false,
                    'format_override'   => false,
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/mock.json/test/param',
                    'format' => 'xml',
                )
            )
        );
    }

    /**
     * @dataProvider negotiateFormatProvider
     */
    public function testSetRoutingNegotiateFormat(
        $uri, array $options, array $expected
    ) {
        $this->assertObjectHasAttribute('route', $this->server);

        $this->request->expects($this->once())
            ->method('getUri')
            ->will($this->returnValue($uri));

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->server->setRouting(
            $this->request,
            array('/whatever' => array()), $options
        );

        $route = $this->server->getRoute();
        $this->assertInstanceOf('Apix\Router', $route);
        $this->assertSame($expected['path'], $route->getPath());

        $response = $this->server->getResponse();
        $this->assertInstanceOf('Apix\Response', $response);
        $this->assertSame($expected['format'], $response->getFormat());
    }

    public function testSetRoutingWithAcceptHeader()
    {
        $uri = '/index.php/api/v1/mock/test/param';
        $options = array(
            'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
            'default_format'    => 'xml',
            'controller_ext'    => false,
            'format_override'   => false,
            'http_accept'       => true,
        );

        $this->request->expects($this->once())
            ->method('getUri')->will($this->returnValue($uri));
        $this->request->expects($this->once())
            ->method('getMethod')->will($this->returnValue('GET'));

        $this->request->expects($this->any())
            ->method('hasHeader')->will($this->returnValue(true));
        $this->request->expects($this->any())
            ->method('getHeader')->will($this->returnValue('text/xml'));

        $this->server->setRouting(
            $this->request, array('/whatever' => array()), $options
        );

        $response = $this->server->getResponse();
        $this->assertSame('Accept', $response->getHeader('Vary'));
        $this->assertSame('xml', $response->getFormat());
    }

    public function testSetRoutingSetVaryWhenAcceptIsEnable()
    {
        $this->request->expects($this->any())
                ->method('getAcceptFormat')
                ->will($this->returnValue('html'));

        $options = array(
            'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
            'default_format'    => 'jsonp',
            'controller_ext'    => false,
            'format_override'   => false,
            'http_accept'       => true,
        );

        $this->server->setRouting(
            $this->request, array('/whatever' => array()), $options
        );
        $this->assertSame(
            'Accept', $this->server->getResponse()->getHeader('Vary'),
            "Vary should be set when accept is enable."
        );
    }

    public function testSetRoutingDoesnotSetVaryWenAcceptIsDisable()
    {
        $options = array(
            'path_prefix'      => '@^(/index.php)?/api/v(\d*)@i',
            'default_format'    => 'jsonp',
            'controller_ext'    => false,
            'format_override'   => false,
            'http_accept'       => false,
        );

        $this->server->setRouting(
            $this->request, array('/whatever' => array()), $options
        );
        $this->assertSame(
            null, $this->server->getResponse()->getHeader('Vary'),
            "Should not be set when accept is disable."
        );
    }

    public function testGetServerVersion()
    {
        $c = array('api_realm' => 'realm', 'api_version'=> 'version');
        $this->assertSame(
            'realm/version (@package_version@)',
            $this->server->getServerVersion($c)
        );
    }

}
