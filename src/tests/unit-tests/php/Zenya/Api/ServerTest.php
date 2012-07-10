<?php

namespace Zenya\Api;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    protected $server, $request;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Zenya\Api\Request')->disableOriginalConstructor()->getMock();

        $this->server = new Server(null, $this->request);
    }

    protected function tearDown()
    {
        unset($this->server);
        unset($this->request);
    }

    public function testRun()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $this->server->run();
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
        $this->assertEquals('defaultExt', $format, "should get default format when all in the negotation chain are set to false");
    }

    public function negotiateFormatProvider()
    {
        return array(
            'controller_ext set to true' => array(
                'uri'=>'/index.php/api/v1/mock.xml/test/param',
                'options' => array(
                    'route_prefix'      => '@^(/index.php)?/api/v(\d*)@i', // regex
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
                    'route_prefix'      => '@^(/index.php)?/api/v(\d*)@i', // regex
                    'default_format'    => 'json',
                    'controller_ext'    => false,
                    'format_override'   => false,
                    'http_accept'       => false, // true or false
                ),
               'expected' => array(
                    'path' => '/mock.json/test/param',
                    'format' => 'json',
                )
            ),
            'format_override set' => array(
                'uri'=>'/index.php/api/v1/mock.json/test/param',
                'options' => array(
                    'route_prefix'      => '@^(/index.php)?/api/v(\d*)@i', // regex
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
                    'route_prefix'      => '@^(/index.php)?/api/v(\d*)@i', // regex
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
            'all false, shodu use default' => array(
                'uri'=>'/index.php/api/v1/mock.json/test/param',
                'options' => array(
                    'route_prefix'      => '@^(/index.php)?/api/v(\d*)@i', // regex
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
    public function testSetRoutingNegotiateFormat($uri, array $options, array $expected)
    {
        $this->assertObjectHasAttribute('route', $this->server);

        $this->request->expects($this->once())->method('getUri')->will($this->returnValue($uri));
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue('GET'));

        $this->server->setRouting($this->request, array('/whatever' => array()), $options);

        $this->assertInstanceOf('Zenya\Api\Router', $this->server->route);
        $this->assertSame($expected['path'], $this->server->route->path); // TODO: getter?

        $this->assertInstanceOf('Zenya\Api\Response', $this->server->response);
        $this->assertSame($expected['format'], $this->server->response->getFormat());
    }

    public function testSetRoutingWithHeader()
    {
        $uri = '/index.php/api/v1/mock/test/param';
        $options = array(
            'route_prefix'      => '@^(/index.php)?/api/v(\d*)@i', // regex
            'default_format'    => 'xml',
            'controller_ext'    => false,
            'format_override'   => false,
            'http_accept'       => true,
        );

        $this->request->expects($this->once())->method('getUri')->will($this->returnValue($uri));
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue('GET'));

        $this->request->expects($this->any())->method('hasHeader')->will($this->returnValue(true));
        $this->request->expects($this->any())->method('getHeader')->will($this->returnValue('text/xml'));

        $this->server->setRouting($this->request, array('/whatever' => array()), $options);

        $this->assertSame('Accept', $this->server->response->getHeader('Vary'));
        $this->assertSame('xml', $this->server->response->getFormat());
    }

    // TODO: resources and config parsinfg related!

}
