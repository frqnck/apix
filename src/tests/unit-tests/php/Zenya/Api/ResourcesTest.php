<?php

namespace Zenya\Api;

use Zenya\Api\Entity,
    Zenya\Api\EntityInterface;

class ResourcesTest extends \PHPUnit_Framework_TestCase
{

    protected $resources, $entity, $route;

    protected function setUp()
    {
        $this->entity = $this->getMock('Zenya\Api\Entity\EntityInterface');

        $this->route = $this->getMock('Zenya\Api\Router');
        $this->route->expects($this->any())
            ->method('getPathName')
            ->will($this->returnValue('/route'));

        $resources = new Resources;
        $resources->setEntity($this->entity);

        $this->resources = $resources;
    }

    protected function tearDown()
    {
        unset($this->resources);
        unset($this->entity);
        unset($this->route);
    }

    /**
     * @covers Zenya\Api\Resources::setEntity
     * @covers Zenya\Api\Resources::getEntity
     */
    public function testGetEntity()
    {
        #$this->resources->setEntity($this->entity);

        $r = new \ReflectionObject($this->resources);
        $p = $r->getProperty('entity');
        $p->setAccessible(true);

        $this->assertSame($this->entity, $this->resources->getEntity());
    }

    /**
     * @covers Zenya\Api\Resources::has
     */
    public function testHasEntityObject($name='whatever')
    {
        $this->resources->add($name, array());
        $this->assertTrue($this->resources->has($name));
        $this->assertFalse($this->resources->has('non-existant'));
    }

    /**
     * @covers Zenya\Api\Resources::toArray
     */
    public function testToArray($name='whatever')
    {
        $this->assertSame(array(), $this->resources->toArray());

        $this->resources->add('name1', array());
        $this->resources->add('name2', array());

        $this->assertSame(2, count($this->resources->toArray()));
        $this->assertArrayHasKey('name1', $this->resources->toArray());
        $this->assertArrayHasKey('name2', $this->resources->toArray());
    }

    /**
     * @covers Zenya\Api\Resources::get
     */
    public function testGetReturnsEntityInterface()
    {
        $this->resources->add('/route', array());
        $this->assertInstanceOf('Zenya\Api\Entity\EntityInterface', $this->resources->get($this->route));
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       404
     */
    public function testGetThrowsInvalidArgumentException()
    {
        // not resources where defined, and none wodu therefore macth
        $this->resources->get($this->route);
    }

    public function testGetFollowsRedirect()
    {
        $this->resources->add('Pierre', array('redirect'=>'Jacques'));
        $this->resources->add('Jacques', array());

        $route = $this->getMock('Zenya\Api\Router');
        $route->expects($this->any(3))
            ->method('getPathName')
            ->will($this->onConsecutiveCalls('Jacques', 'Pierre'));
        $this->assertEquals($this->resources->get($route), $this->resources->get($route), 'Pierre should be equal to Jacques');
    }

    public function testGetFollowsSubsequentRedirect()
    {
        $this->resources->add('Paul', array('redirect'=>'Pierre'));
        $this->resources->add('Pierre', array('redirect'=>'Jacques'));
        $this->resources->add('Jacques', array());

        $route = $this->getMock('Zenya\Api\Router');
        $route->expects($this->once())
            ->method('getPathName')
            ->will($this->returnValue('Paul'));

        $this->assertEquals('Jacques', $this->resources->get($route)->getRedirect(), 'Paul follows a redirect from Pierre, and equals to Jacques');
    }


    public function testGetDoNotRedirect()
    {
        $this->resources->add('Paul', array('redirect'=>'Pierre'));
        $this->resources->add('Pierre', array('redirect'=>'Jacques'));
        $this->resources->add('Jacques', array());

        $route = $this->getMock('Zenya\Api\Router');
        $route->expects($this->exactly(2))
            ->method('getPathName')
            ->will($this->onConsecutiveCalls('Jacques', 'Paul'));
        $this->assertNotEquals($this->resources->get($route), $this->resources->get($route), 'Paul should not be equal to Jacques');
    }

#######
##### TODO: review below when we refactor!
#######

    /**
     * @covers Zenya\Api\Resources::add
     */
    public function testAddReturnAnEntityObject()
    {
        $entity = $this->resources->add('name', array());
        $this->assertInstanceOf('Zenya\Api\Entity\EntityInterface', $entity);
    }

    public function testAddClosureObject()
    {
        $this->resources->add('/route', array('action'=>function(){return 'string';}, 'method'=>'some'));
        $this->assertInstanceOf('Zenya\Api\Entity\EntityClosure', $this->resources->get($this->route));
    }

    public function testAddClassObject()
    {
        // TODO: use 'controller' instead of default to class...
        $this->resources->add('/route', array());
        $this->assertInstanceOf('Zenya\Api\Entity\EntityClass', $this->resources->get($this->route));
    }

    public function testAddGroupObject()
    {
        $this->markTestIncomplete('TODO: group objcts!');

        // $this->resources->add('group', array('group'=>function(){return 'string';}, 'method'=>'some'));
        // $this->assertInstanceOf('Zenya\Api\Entity\EntityClosure', $this->resources->get('closure'));
    }


}