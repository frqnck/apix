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
        $this->server->run();
    }

    ##
    # format_negotiation
    ##

    public function testGetNegotiatedFormatUseDefault()
    {
        $c = array(
            'default'        => 'json',
            'controller_ext' => false,
            'request_chain'  => false, #isset($_REQUEST['format']) ? $_REQUEST['format'] : false,
            'http_accept'    => false,
        );
        $format = $this->server->getNegotiatedFormat($c);
        $this->assertEquals('json', $format, "should get default format when negotation chain is off");
    }

    public function testGetNegotiatedFormatUseControllerExtension()
    {
        $c = array(
            'default'        => 'json',
            'controller_ext' => true,
            'request_chain'  => false, #isset($_REQUEST['format']) ? $_REQUEST['format'] : false,
            'http_accept'    => false,
        );
        $format = $this->server->getNegotiatedFormat($c);
        $this->assertEquals('json', $format, "should get default format when negotation chain is off");
    }

}
