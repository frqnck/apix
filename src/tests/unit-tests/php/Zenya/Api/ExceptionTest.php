<?php
namespace Zenya\Api;

class ExceptionTest extends \PHPUnit_Framework_TestCase
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

    // public function testAppend()
    // {
    //     $entity = $this->entity->toArray();

    //     $this->assertSame($this->definition['controller'], $entity['controller']);
    //     $this
    // }
}