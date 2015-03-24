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

class ServerTest extends TestCase
{

    protected $server, $request;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Apix\HttpRequest')
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->server = new Server(null, $this->request);

        $this->closure = function () {};
    }

    protected function tearDown()
    {
        unset($this->server, $this->request);
    }

    /**
     * @dataProvider onMethodProvider
     */
    public function testOnMethodProxy($name, $method)
    {
        $entity = $this->server->$name('/path', $this->closure);
        $this->assertInstanceOf('Apix\Entity', $entity);
        $this->assertTrue($entity->hasMethod($method));
    }

    public function onMethodProvider()
    {
        return array(
            array('onCreate', 'POST'),
            array('onRead', 'GET'),
            array('onUpdate', 'PUT'),
            array('onModify', 'PATCH'),
            array('onDelete', 'DELETE'),
            array('onHelp', 'OPTIONS'),
            array('onTest', 'HEAD')
        );
    }

    // public function testSetGroup()
    // {
    //     $entity = $this->server->onRead('/path', $this->closure);

    //     // $entity = $this->server->setGroup('dddd');

    //     $entity->group = 'test';
    //     // $this->assertInstanceOf('Apix\Entity', $entity);

    //     // $this->assertInstanceOf('Apix\Entity', $entity);
    //     // $this->assertTrue($entity->hasMethod($method));
    // }

}
