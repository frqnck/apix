<?php

namespace Zenya\Api;

use Zenya\Api\Entity;

class ResourcesTest extends \PHPUnit_Framework_TestCase
{

    protected $resources, $mockEntity;

    protected function setUp()
    {
        $this->mockEntity = $this->getMock('Zenya\Api\Entity\EntityInterface');
        $this->mockEntity->expects($this->any())->method('append')->will($this->returnValue(0));
        $this->mockEntity->expects($this->any())->method('getRedirect')->will($this->returnValue("test"));
        
        $resources = new Resources;
        $resources->setEntity($this->mockEntity);
    
        $this->resources = $resources;
    }

    protected function tearDown()
    {
        unset($this->resources);
    }

    /**
     * @covers Zenya\Api\Resources::setEntity
     * @covers Zenya\Api\Resources::getEntity
     */
    public function testSetEntity()
    {
        $this->resources->setEntity($this->mockEntity);
        $this->assertSame($this->mockEntity, $this->resources->getEntity());
    }

    /**
     * @covers Zenya\Api\Resources::add
     */
    public function testAddReturnAnEntityObject()
    {
        $entity = $this->resources->add('name', array());
        $this->assertInstanceOf('Zenya\Api\Entity\EntityInterface', $entity);
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
    public function testGet()
    {
        $this->resources->add('name', array('test'));
        $this->assertInstanceOf('Zenya\Api\Entity\EntityInterface', $this->resources->get('name'));
    }

    public function testGetFollowsAliases()
    {
        $this->assertEquals('test', $this->mockEntity->getRedirect());

        $this->markTestIncomplete(
            'Not working - test has not been implemented yet.'
        );


        $this->resources->add('paul', array());
        $this->resources->add('pierre', array());


        $this->assertEquals($this->resources->get('paul'), $this->resources->get('pierre'));
    }

}