<?php

namespace Zenya\Api;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zenya_Api_Server
     */
    protected $server;

    protected function setUp()
    {
        $this->server = new Server;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->server);
    }

    /**
     * @covers Zenya\Api\Server::__construct
     */
    public function testConstructor()
    {
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
        $this->server->run();
    }

}