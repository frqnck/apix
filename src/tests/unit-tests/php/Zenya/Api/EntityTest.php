<?php
namespace Zenya\Api;

use Zenya\Api\Entity,
    Zenya\Api\Router,
    Zenya\Api\Entity\EntityInterface;

class EntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $entity, $route;

    protected function setUp()
    {
        #$this->entity = new Entity;
        $this->entity = $this->getMock('Zenya\Api\Entity', array('underlineCall', '_parseDocs'));

        $this->route = $this->getMock('Zenya\Api\Router', array('getMethod'));

        $this->entity->setRoute($this->route);
    }

    protected function tearDown()
    {
        unset($this->entity);
    }

    public function testAppend()
    {
        $this->entity->_append(array('redirect'=>'test'));

        $entity = $this->entity->toArray();
        $this->assertSame('test', $entity['redirect']);
    }

    public function testCall()
    {
        $this->entity->expects($this->once())
                ->method('underlineCall')
                ->will($this->returnValue(array('calledData')));

        $this->assertEquals(array('calledData'), $this->entity->call(), "Checking mocking actually work.");
    }

    /**
     * @todo return the help for now, will need to redirect the entity instead... using clone!
     */
    public function testCallWithOptionsReturnDocs()
    {
        $this->entity->expects($this->once())
                ->method('_parseDocs')
                ->will($this->returnValue(array('parseDocs')));

        $this->route->expects($this->once())
                ->method('getMethod')
                ->will($this->returnValue('OPTIONS'));

        $this->assertEquals(array('parseDocs'), $this->entity->call(), "Checking mocking actually work.");
    }

    public function testGetDocs()
    {
        $docs = array('parseDocs', 'methods'=>array('GET'=>'doc for GET'));
        $this->entity->expects($this->once())
                ->method('_parseDocs')
                ->will($this->returnValue($docs));

        $this->assertSame($docs, $this->entity->getDocs());
        $this->assertSame($docs['methods']['GET'], $this->entity->getDocs('GET'));
    }

    /**
     * @expectedException           \BadMethodCallException
     * @expectedExceptionCode       400
     */
    public function testGetRequiredParams()
    {
        $refClass = new \ReflectionClass('Zenya\Api\Fixtures\DocbookClass');
        $refMethod = $refClass->getMethod('methodNameTwo');

        $params = $this->entity->getRequiredParams($refMethod, 'aStringName', array('arg1'=>123));
        $this->assertSame(array('arg1'=>123), $params);

        $params = $this->entity->getRequiredParams($refMethod, 'aStringName', array('arg2'=>345));
    }

    public function testSetRoute()
    {
        $this->entity->setRoute($this->route);
        $this->assertAttributeEquals($this->route, 'route', $this->entity);
    }

    public function testGetRedirect()
    {
        $this->entity->_append(array('redirect'=>'test'));

        $this->assertSame('test', $this->entity->getRedirect());
    }

    public function testIsPublic()
    {
        $this->assertTrue($this->entity->isPublic());

        $this->entity = $this->getMock('Zenya\Api\Entity', array('getDocs'));
        $this->entity->setRoute($this->route);

        $this->entity->expects($this->exactly(3))
                ->method('getDocs')
                ->will($this->onConsecutiveCalls(
                    array('api_role'=>'admin'),
                    array('api_role'=>'public'),
                    array('api_role'=>null)
                ));

        $this->assertFalse($this->entity->isPublic());
        $this->assertTrue($this->entity->isPublic());
        $this->assertTrue($this->entity->isPublic());
     }

}