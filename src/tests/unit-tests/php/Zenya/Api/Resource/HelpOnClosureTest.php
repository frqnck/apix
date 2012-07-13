<?php
namespace Zenya\Api;

use Zenya\Api\Server;

class HelpOnClosureTest extends \PHPUnit_Framework_TestCase
{

    protected $api, $help;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Zenya\Api\Request')->disableOriginalConstructor()->getMock();

        $this->api = new Server(false, $this->request);

        $this->api->onCreate('/create/:test',
            /**
             * Create something...
             *
             * @return array  The array to return to the client
             */
            function($test) {
                return array("You just created $test.");
            }
        );

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

        $this->api->setRouting(
            $this->request,
            $this->api->resources->toArray()
        );


        $this->help = new Resource\Help($this->api);
    }

    protected function tearDown()
    {
        unset($this->api);
        unset($this->help);
    }

    public function testSetRouteName()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/', '/unit/:test', '/*') );

        $this->help->setRouteName($this->api);
        $this->assertSame(null, $this->api->getRoute()->getName());

        $this->help->setRouteName($this->api);
        $this->assertSame('/unit/:test', $this->api->getRoute()->getName());
    }

    public function testOnReadReturnsArrayForOneEntity()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/unit/:test') );

        $results = $this->help->onRead($this->api);

        $this->assertArrayHasKey('GET', $results['methods']);
        $this->assertArrayHasKey('PATCH', $results['methods']);
        $this->assertEquals(3, count($results));
    }

    protected function genericTest($results)
    {
        $this->assertArrayHasKey('/unit/:test', $results);
        $this->assertArrayHasKey('GET', $results['/unit/:test']['methods']);
        $this->assertArrayHasKey('PATCH', $results['/unit/:test']['methods']);

        $this->assertArrayHasKey('/create/:test', $results);
        $this->assertArrayHasKey('POST', $results['/create/:test']['methods']);
    }

    public function testOnReadReturnsArrayForAllEntities()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/') );

        $results = $this->help->onRead($this->api);
        $this->assertTrue( count($results)>1 );

        $this->genericTest($results);
    }

    public function testOnHelpRetrieveAllTheEntities()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/*') );

        $results = $this->help->onHelp($this->api);

        $this->genericTest($results);
    }

    public function testVerboseOutput()
    {
        $_REQUEST['verbose'] = true;
        $this->help = new Resource\Help($this->api);

        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/*') );

        $results = $this->help->onHelp($this->api);

        $this->assertArrayHasKey('operator-manual', $results['/unit/:test']);
        $this->assertArrayHasKey('end-user-manual', $results['/unit/:test']);
    }

}