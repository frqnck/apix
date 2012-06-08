<?php
require_once APP_TOPDIR . '/Zenya/Api/Server.php';

use Zenya\Api;

class ResourceTest extends \PHPUnit_Framework_TestCase
{

    protected $resource;

    public $resources = array('BlankResource'=>'Zenya\Api\Resource\BlankResource');

    protected function setUp()
    {
        $server = new Zenya\Api\Server;
        $this->resource = new Zenya\Api\Resource($server);
    }

    protected function tearDown()
    {
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
        $routes = array(
            '/:controller/:paramUn' => array(),
        );
        $route = new Zenya\Api\Router($routes);
        $route->map('/controller/paramUn');
        $route->setMethod('GET');
        $this->resource->setRouteOverrides($route);
        $this->assertEquals('controller', $route->getControllerName());
    }

    /**
     * @covers Zenya\Api\Resource::SetRouteOverrides
     */
    public function testSetRouteOverridesTestOnHead()
    {
        $routes = array(
            '/:controller/:arg1' => array(),
        );
        $route = new Zenya\Api\Router($routes);
        $route->map('/resourceName/param1');
        $route->setMethod('HEAD');
        $this->resource->setRouteOverrides($route);
        
        $this->assertEquals('test', $route->getControllerName());
    }

    /**
     * @covers Zenya\Api\Resource::SetRouteOverrides
     */
    public function testSetRouteOverridesHelpOnOptions()
    {
        $routes = array(
            '/:controller/:arg1' => array(),
        );
        $route = new Zenya\Api\Router($routes);
        $route->map('/resourceName/param1');
        $route->setMethod('OPTIONS');
        $this->resource->setRouteOverrides($route);
        
        $this->assertEquals('help', $route->getControllerName());

#        $params = $route->params; // TODO: review!
#        $this->assertEquals('resourceName', $params['name']);
#        $this->assertSame('resourceName', $params['params']);
    }

    /**
     * @covers Zenya\Api\Resource::call
     */
    public function testCall()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
        $this->assertSame( $this->resources, $this->Obj->getResources() );
    }

    /**
     * @covers Zenya\Api\Resource::getMethods
     */
    public function testGetMethods()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');

        $routes = array(
            '/:controller/:arg1' => array(),
        );
        $route = new Zenya\Api\Router($routes);
        $route->map('/resourceName/param1');
        $route->setMethod('OPTIONS');

        $this->assertSame('', $this->resource->getMethods($route) );
    }

}