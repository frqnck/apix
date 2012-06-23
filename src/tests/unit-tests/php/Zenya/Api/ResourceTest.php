<?php

namespace Zenya\Api;

class ResourceTest extends \PHPUnit_Framework_TestCase
{

    protected $resource, $routes;

    protected function setUp()
    {
        $routes = array('/:controller/:id/:optional' => array());
        $this->route = new Router($routes);
        $this->route->map('/controller/123');
        $this->route->setMethod('GET');
        $this->resource = new Resource($this->route);
    }

    protected function tearDown()
    {
        unset($this->resource);
    }

    /**
     * @covers Zenya\Api\Resource::__construct
     */
    public function testConstructor()
    {
        // TODO: test listeners!
    }

    /**
     * @covers Zenya\Api\Resource::SetRouteOverrides
     */
    public function testSetRouteOverridesOnGet()
    {
        $this->resource->setRouteOverrides($this->route);
        $this->assertEquals('controller', $this->route->getController());
    }

    /**
     * @covers Zenya\Api\Resource::SetRouteOverrides
     */
    public function testSetRouteOverridesTestOnHead()
    {
        $routes = array(
            '/:controller/:arg1' => array(),
        );
        $route = new Router($routes);
        $route->map('/resourceName/param1');
        $route->setMethod('HEAD');
        $this->resource->setRouteOverrides($route);

        $this->assertEquals('test', $route->getController());

/* todo review        
        $params = $route->getParams(); // TODO: review!
        $this->assertSame('resourceName', $params['name']);
        $this->assertSame(array('controller' => 'resourceName', 'arg1' => 'param1'), $params['params']);
*/
    }

    /**
     * @covers Zenya\Api\Resource::SetRouteOverrides
     * @todo
     */
    public function testSetRouteOverridesHelpOnOptions()
    {
        $routes = array(
            '/:controller/:arg1' => array(),
        );
        $route = new Router($routes);
        $route->map('/resourceName/param1');
        $route->setMethod('OPTIONS');
        $this->resource->setRouteOverrides($route);

        $this->assertEquals('help', $route->getController());

        $params = $route->getParams(); // TODO: review!
        #$this->assertSame('resourceName', $params['name']);
        #$this->assertSame(array('controller' => 'resourceName', 'arg1' => 'param1'), $params['params']);
    }

    /**
     * @expectedException           \BadMethodCallException
     * @expectedExceptionCode       400
     */
    public function testGetRequiredParams()
    {
        $refClass = new ReflectionClass('Zenya\Api\Fixtures\DocbookClass');
        $refMethod = $refClass->getMethod('methodNameTwo');

        $params = $this->resource->getRequiredParams('methodNameTwo', $refMethod, array('arg1'=>123));
        $this->assertSame(array('arg1'=>123), $params);

        $params = $this->resource->getRequiredParams('methodNameTwo', $refMethod, array('arg2'=>345));
    }

    public function testCall()
    {
        $resource = array(
            'controller' => array(
                'name' => 'Zenya\Api\Fixtures\CrudClass'
            )
        );

        $results = $this->resource->call($resource);
        $this->assertSame(array('123'), $results );
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       405
     */
    public function testCallThrowsInvalidArgumentException()
    {
        $routes = array('/:controller/:id/:optional' => array());
        $route = new Router($routes);
        $route->map('/controller/id');
        $route->setMethod('XXX');
        $resource = new Resource($route);

        $r = array(
            'controller' => array(
                'name' => 'Zenya\Api\Fixtures\CrudClass'
            )
        );

        $results = $resource->call($r);
    }

    /**
     * @expectedException           \BadMethodCallException
     * @expectedExceptionCode       400
     */
    public function testCallThrowsBadMethodCallException()
    {
        $routes = array('/:controller/:id/:optional' => array());
        $route = new Router($routes);
        $route->map('/controller');
        $route->setMethod('GET');
        $resource = new Resource($route);

        $r = array(
            'controller' => array(
                'name' => 'Zenya\Api\Fixtures\CrudClass'
            )
        );

        $results = $resource->call($r);
    }
}
