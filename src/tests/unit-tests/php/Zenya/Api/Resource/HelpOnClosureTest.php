<?php
namespace Zenya\Api;

use Zenya\Api\Server;

class HelpOnClosureTest extends \PHPUnit_Framework_TestCase
{

    protected $api, $help;

    protected function setUp()
    {
        $this->api = new Server;

        $this->api->onRead('/unit/:test',
            /**
             * Read/retrieve something...
             *
             * @return array  The array to return to the client
             */
            function($test) {
                return array("You just retrieve $test.");
            }
        );

        $this->api->onModify('/unit/:test',
            /**
             * Modify that something...
             *
             * @return array  The array to return to the client
             */
            function($test) {
                return array("$test was modified.");
            }
        );
        #$this->api->run();

        $this->help = new Resource\Help($this->api);
    }

    protected function tearDown()
    {
        unset($this->api);
        unset($this->help);
    }

    public function testOnHelpRetrieveOneEntity()
    {
        $route = new Router(array('/unit/:test'=>array()));
        $route->map('/unit/test');

        $entity = $this->api->resources->get($route);

        $help = $this->help->onHelp($entity);
        #print_r($help);
    }

    public function testOnHelpRetrieveAllTheEntities()
    {
        #$route = $this->api->getRoute();
        #$route->map('/unit/test');

        $route = new Router(array('/*'=>array()));
        $route->map('/*');

        $entity = $this->api->resources->get($route);

        $help = $this->help->onHelp($entity);
        print_r($help);
    }

}