<?php
namespace Apix;

class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    public $definition = array('controller'=>array('name'=>'Apix\Fixtures\CrudClass', 'args'=>array()),'redirect'=>'location');
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
        unset($this->entity, $this->route);
    }

    public function testAppend()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

    //     $entity = $this->entity->toArray();

    //     $this->assertSame($this->definition['controller'], $entity['controller']);
    //     $this
    }
}
