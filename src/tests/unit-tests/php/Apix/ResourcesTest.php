<?php

namespace Apix;

use Apix\Entity,
    Apix\EntityInterface;

class ResourcesTest extends \PHPUnit_Framework_TestCase
{

    protected $resources, $route;

    protected function setUp()
    {
        $resources = new Resources();
        $resources->setEntity(
            $this->getMock('Apix\Entity\EntityInterface')
        );
        $resources->add('/paris', array('action'=>function () {return 'metro';}, 'method'=>'some'));

        $this->resources = $resources;

        $this->route = $this->getMock('Apix\Router');

        $this->route->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('/paris'));
    }

    protected function tearDown()
    {
        unset($this->resources, $this->route);
    }

    /**
     * @covers Apix\Resources::setEntity
     * @covers Apix\Resources::getEntity
     */
    public function testGetEntity()
    {
        $r = new \ReflectionObject($this->resources);
        $entity = $r->getProperty('entity');
        $entity->setAccessible(true);

        $this->assertSame(
            $entity->getValue($this->resources),
            $this->resources->getEntity()
        );
    }

    /**
     * @covers Apix\Resources::has
     */
    public function testHasEntityObject($name='mango')
    {
        $this->resources->add($name, array());
        $this->assertTrue($this->resources->has($name));
        $this->assertFalse($this->resources->has('non-existant'));
    }

    /**
     * @covers Apix\Resources::toArray
     */
    public function testToArray()
    {
        $resources = new Resources();
        $this->assertSame(array(), $resources->toArray());

        $resources->add('name1', array());
        $resources->add('name2', array());
        $this->assertSame(2, count($resources->toArray()));
        $this->assertArrayHasKey('name1', $resources->toArray());
        $this->assertArrayHasKey('name2', $resources->toArray());
    }

    /**
     * @covers Apix\Resources::get
     */
    public function testGetReturnsEntityInterface()
    {
        $this->assertInstanceOf('Apix\Entity\EntityInterface', $this->resources->get($this->route));
    }

    /**
     * Should throw a 404, Invalid resource entity specified.
     *
     * @expectedException           \DomainException
     * @expectedExceptionCode       404
     */
    public function testGetThrowsDomainException()
    {
        $route = $this->getMock('Apix\Router');
        $this->resources->get($route);
    }

    public function testGetFollowsRedirect()
    {
        $this->resources->add('/milan', array('redirect'=>'/paris'));

        $route = $this->getMock('Apix\Router');
        $route->expects($this->any(3))
            ->method('getName')
            ->will($this->onConsecutiveCalls('/paris', '/milan'));

        $this->assertEquals($this->resources->get($route), $this->resources->get($route), '/milan should be equal to /paris');
    }

    public function testGetFollowsAllTheSubsequentRedirects()
    {
        $this->resources->add('/pierre', array('redirect'=>'/paul'));
        $this->resources->add('/paul', array('redirect'=>'/jacques'));
        $this->resources->add('/jacques', array('redirect'=>'/paris'));

        $route = $this->getMock('Apix\Router');
        $route->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('/pierre'));

        $this->assertEquals('/jacques', $this->resources->get($route)->getRedirect(), '/pierre follows a redirect from /paul to /jacques');
    }

    public function testGetDoNotRedirect()
    {
        $this->resources->add('/pierre', array('redirect'=>'/paul'));
        $this->resources->add('/paul', array('redirect'=>'/jacques'));
        $this->resources->add('/jacques', array('redirect'=>'/paris'));

        $route = $this->getMock('Apix\Router');
        $route->expects($this->exactly(2))
            ->method('getName')
            ->will($this->onConsecutiveCalls('/jacques', '/paul'));

        $this->assertNotEquals($this->resources->get($route), $this->resources->get($route), '/pierre should not be equal to /jacques');
    }

    /**
     * @covers Apix\Resources::add
     */
    public function testAddReturnAnEntityObject()
    {
        $entity = $this->resources->add('name', array());
        $this->assertInstanceOf('Apix\Entity\EntityInterface', $entity);
    }

    public function testAddClosureObject()
    {
        $this->assertInstanceOf('Apix\Entity\EntityClosure', $this->resources->get($this->route));
    }

    public function testAddClassObject()
    {
        $this->resources->add('/london', array(
            'controller' => array(
                'name' => __NAMESPACE__ . '\Fixtures\BlankResource',
            )
        ));

        $route = $this->getMock('Apix\Router');
        $route->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('/london'));

        $this->assertInstanceOf('Apix\Entity\EntityClass', $this->resources->get($route));
    }

    public function testRedirectToTheHelpResourceOnHelpRequest()
    {
        $this->resources->add('help', array(
            'controller' => array(
                'name' => __NAMESPACE__ . '\Resource\Help',
            )
        ));

        $this->route->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('OPTIONS'));

        $this->assertSame($this->resources->getResource('help'), $this->resources->get($this->route));
    }

    public function testDoNotRedirectToTheHelpResourceOnHelpRequestWhenFollowIsFalse()
    {
        $this->resources->add('help', array(
            'controller' => array(
                'name' => __NAMESPACE__ . '\Resource\Help',
            )
        ));

        $this->route->expects($this->any())
            ->method('getMethod')
            ->will($this->returnValue('OPTIONS'));

        $this->assertSame($this->resources->getResource('/paris'), $this->resources->get($this->route, false));
    }

    public function testHeadRequestSetRouteMethodToGet()
    {
        // with GET
        $this->resources->add('help', array(
            'controller' => array(
                'name' => __NAMESPACE__ . '\Resource\Help',
            )
        ));

        // without GET
        $this->resources->add('test', array(
            'controller' => array(
                'name' => __NAMESPACE__ . '\Resource\Test',
            )
        ));
        // $this->route->expects($this->any())
        //     ->method('getMethod')
        //     ->will($this->returnValue('HEAD'));
        $route = new Router();

        $route->setMethod('HEAD');
        $route->setName('help');
        $entity = $this->resources->get($route);
        $this->assertSame('GET', $route->getMethod(), 'HEAD should forward to GET');

        $route->setMethod('HEAD');
        $route->setName('test');
        $entity = $this->resources->get($route);
        $this->assertSame('HEAD', $route->getMethod(), 'HEAD should not be forwarded if GET is not defined.');

    }

#######
##### TODO: review below when we refactor!
#######

    public function testAddGroupObject()
    {
        $this->markTestIncomplete('TODO: grouping of objcts!');

        // $this->resources->add('group', array('group'=>function () {return 'string';}, 'method'=>'some'));
        // $this->assertInstanceOf('Apix\Entity\EntityClosure', $this->resources->get('closure'));
    }

}
