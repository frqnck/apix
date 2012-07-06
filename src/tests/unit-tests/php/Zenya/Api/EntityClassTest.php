<?php
namespace Zenya\Api;

use Zenya\Api\Entity,
    Zenya\Api\Router,
    Zenya\Api\Entity\EntityInterface,
    Zenya\Api\Entity\EntityClass;

class EntityClassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    public $definition = array('controller'=>array('name'=>'Zenya\Api\Fixtures\CrudClass', 'args'=>array()),'redirect'=>'location');
    protected $entity, $route;

    protected function setUp()
    {
        $this->entity = new Entity\EntityClass;
        $this->entity->append($this->definition);

        $routes = array('/:controller/:id/:optional' => array());
        $this->route = new Router($routes);
        $this->route->setMethod('GET');
    }

    protected function tearDown()
    {
        unset($this->entity);
        unset($this->route);
    }

    public function testAppend()
    {
        $entity = $this->entity->toArray();

        $this->assertSame($this->definition['controller'], $entity['controller']);
        $this->assertSame('location', $entity['redirect'], "Check to see if parent::_append is called.");
    }

    public function testUnderlineCall()
    {
        $this->route->map('/controller/1234');

        $results = $this->entity->underlineCall($this->route);
        $this->assertSame(array('1234'), $results);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       405
     */
    public function testCallThrowsInvalidArgumentException()
    {
        $this->route->map('/controller/id');
        $this->route->setMethod('XXX');
        $this->entity->underlineCall($this->route);
    }

    /**
     * @expectedException           \BadMethodCallException
     * @expectedExceptionCode       400
     */
    public function testCallThrowsBadMethodCallException()
    {
        $this->route->map('/controller');
        $this->entity->underlineCall($this->route);
    }

    public function testParseDocsClassLevel()
    {
        $docs = $this->entity->_parseDocs();
        $this->assertSame('CRUD fixture class', $docs['title']);
        $this->assertSame(4, count($docs['methods']));
    }

    public function testGetMethod()
    {
        $method = $this->entity->getMethod($this->route);
        $this->assertInstanceOf('ReflectionMethod',  $method, "Shoulf be a ReflectionMethod instance");
        $this->assertSame('onRead', $method->getShortName());
    }

    public function testGetActions()
    {
        $actions = $this->entity->getActions();
        $this->assertSame(4, count($actions));
    }

    public function testGetMethods()
    {
        $actions = $this->entity->getMethods();
        $method = $actions[1];
        $this->assertInstanceOf('ReflectionMethod', $method, "Shoulf be a ReflectionMethod instance");
        $this->assertSame('onRead', $method->getShortName());
        $this->assertSame(4, count($actions));
    }

    public function testReflectedClass()
    {
        $class = $this->entity->reflectedClass();
        $this->assertInstanceOf('ReflectionClass', $class, "Shoulf be a ReflectionClass instance");

        $this->assertSame($class, $this->entity->reflectedClass());
    }

    /**
     * @expectedException           \RuntimeException
     * @expectedExceptionCode       501
     */
    public function testReflectedClassReturnsFalse()
    {
        $bad_definition = array('controller'=>array('name'=>'ClassThatDoesNotExist(YET?)', 'args'=>array()));
        $this->entity->append($bad_definition);
        $this->entity->reflectedClass();
    }

}