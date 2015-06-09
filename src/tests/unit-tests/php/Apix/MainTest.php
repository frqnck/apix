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
        unset($this->server, $this->request);
    }

    /**
     * @expectedException           \DomainException
     * @expectedExceptionCode       406
     */
    public function testNegotiateFormatUseDefaultAndthrowsDomainException()
    {
        $c = array(
            'default_format'    => 'defaultExt',
            'allow_extension'    => false,
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
            'allow_extension (from end) set to true' => array(
                'uri' => '/index.php/api/v1/foo/bar/baz.xml',
                'options' => array(
                    'path_prefix'       => '@^(/index.php)?/api/v(\d*)@i',
                    'default_format'    => 'xml',
                    'allow_extension'    => true,
                    'format_override'   => false,
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/foo/bar/baz',
                    'format' => 'xml',
                )
            ),
            'allow_extension set to false' => array(
                'uri' => '/api/v1/foo/bar/baz.json',
                'options' => array(
                    'path_prefix'       => '@/api/v(\d*)@i',
                    'default_format'    => 'json',
                    'allow_extension'    => false,
                    'format_override'   => false,
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/foo/bar/baz.json',
                    'format' => 'json',
                )
            ),
            'format_override set' => array(
                'uri' => '/api/v1/foo/bar/baz.json',
                'options' => array(
                    'path_prefix'       => '@/api/v(\d*)@i',
                    'default_format'    => 'json',
                    'allow_extension'    => false,
                    'format_override'   => 'html',
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/foo/bar/baz.json',
                    'format' => 'html',
                )
            ),
            'http_accept is set (but none provided)' => array(
                'uri' => '/api/v1/foo/bar/baz.json',
                'options' => array(
                    'path_prefix'       => '@/api/v(\d*)@i',
                    'default_format'    => 'xml',
                    'allow_extension'    => false,
                    'format_override'   => false,
                    'http_accept'       => true,
                ),
               'expected' => array(
                    'path' => '/foo/bar/baz.json',
                    'format' => 'xml',
                )
            ),
            'all false, should use default' => array(
                'uri' => '/api/v1/foo/bar/baz.json',
                'options' => array(
                    'path_prefix'       => '@/api/v(\d*)@i',
                    'default_format'    => 'xml',
                    'allow_extension'    => false,
                    'format_override'   => false,
                    'http_accept'       => false,
                ),
               'expected' => array(
                    'path' => '/foo/bar/baz.json',
                    'format' => 'xml',
                )
            )
        );
    }

    /**
     * @group tt
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
        $uri = 'api/v1/foo/bar/baz';
        $options = array(
            'default_format'    => 'xml',
            'allow_extension'    => false,
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
            'default_format'    => 'jsonp',
            'allow_extension'    => false,
            'format_override'   => false,
            'http_accept'       => true,
        );

        $this->server->setRouting(
            $this->request, array('/whatever' => array()), $options
        );
        $this->assertSame(
            'Accept', $this->server->getResponse()->getHeader('Vary'),
            'Vary should be set when accept is enable.'
        );
    }

    public function testSetRoutingDoesnotSetVaryWenAcceptIsDisable()
    {
        $options = array(
            'default_format'    => 'jsonp',
            'allow_extension'    => false,
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
        $this->assertSame(
            'realm/version (' . Server::VERSION . ')',
            $this->server->getServerVersion(
                array('api_realm' => 'realm', 'api_version'=> 'version')
            )
        );
    }

}
