<?php

namespace Zenya\Api;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    protected $server;

    protected function setUp()
    {
        $this->server = new Server;
    }

    protected function tearDown()
    {
        unset($this->server);
    }

    public function testConstructor()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        #$this->server->run();
    }

    public function testRun()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        $this->server->run();
    }

    public function testSetRouting()
    {
        $m = $this->getMock('Zenya\Api\Request');

        $m->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue('/test.php'));

        $m->expects($this->any())
            ->method('getMethod')
            ->will($this->onConsecutiveCalls('GET', 'POST', 'PUT', 'OPTIONS' ));

        $this->assertObjectHasAttribute('route', $this->server);
        $this->assertSame(null, $this->server->route);

        $this->server->setRouting(new $m);
        $this->assertInstanceOf('Zenya\Api\Router', $this->server->route);

       # $this->assertSame('Zenya\Api\Router', $this->server->path);

        #$this->assertSame(array('foo', 'bar'), $this->server->extractExtension('foo.bar'));
        #$this->assertSame(array('f', 'bar'), $this->server->extractExtension('f.o.o.bar'));
        #$this->assertSame(false, $this->server->extractExtension('foobar'));
    }

    public function testGetFormatFromHttpAccept()
    {
        $m = $this->getMock('Zenya\Api\Request');

        $m->expects($this->any())
            ->method('hasHeader')
            ->will($this->returnValue(true));

        $m->expects($this->any())
            ->method('getHeader')
            ->will($this->onConsecutiveCalls('application/json', 'application/xml', 'text/xml', 'text/html' ));

        $this->assertEquals('json', $this->server->getFormatFromHttpAccept($m));
        $this->assertEquals('xml', $this->server->getFormatFromHttpAccept($m));
        $this->assertEquals('xml', $this->server->getFormatFromHttpAccept($m));
        $this->assertEquals(false, $this->server->getFormatFromHttpAccept($m));

    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       406
     */
    public function testNegotiateFormatUseDefaultAndthrowsException()
    {
        $c = array(
            'default'        => 'defaultExt',
            'controller_ext' => false,
            'override'       => false,
            'http_accept'    => false,
        );
        $format = $this->server->negotiateFormat($c);
        $this->assertEquals('defaultExt', $format, "should get default format when all in the negotation chain are set to false");
    }

    public function offtestNegotiateFormatUse()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $this->server->setRouting();

        $m = $this->getMock('Zenya\Api\Server', array('extractExtension'));

        $m->expects($this->any())
            ->method('extractExtension')
            ->will($this->returnValue(array('foo','bar')));

        $c = array(
            'default'        => 'json',
            'controller_ext' => true,
            'override'       => false, #isset($_REQUEST['format']) ? $_REQUEST['format'] : false,
            'http_accept'    => false,
        );

        $format = $m->negotiateFormat($c);
        $this->assertEquals('defaultExt', $format);
    }

    // public function testGetResults()
    // {
    //     $this->assertSame(null, $this->server->getResults());
    // }

}
