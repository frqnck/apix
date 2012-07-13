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

    public function testOnReadSetRouteName()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/', '/unit/:test', '/*') );

        $this->help->onRead($this->api);
        $this->assertSame('/', $this->api->getRoute()->getName());

        $this->help->onRead($this->api);
        $this->assertSame('/unit/:test', $this->api->getRoute()->getName());
    }

    public function testOnReadReturnsArrayForOneEntity()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/unit/:test') );

        $results = $this->help->onRead($this->api);
        $res = $results[$this->help->doc_nodeName];

        $this->assertArrayHasKey('path', $res);
        $this->assertArrayHasKey('GET', $res['methods']);
        $this->assertArrayHasKey('PATCH', $res['methods']);
        $this->assertEquals(4, count($res));
    }

    protected function genericTest($results)
    {
        $this->assertSame('/create/:test', $results[2]['path']);
        $this->assertArrayHasKey('POST', $results[2]['methods']);

        $this->assertSame('/unit/:test', $results[3]['path']);
        $this->assertArrayHasKey('GET', $results[3]['methods']);
        $this->assertArrayHasKey('PATCH', $results[3]['methods']);
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

        #$this->assertArrayHasKey($this->help->private_nodeName, $results['/unit/:test']);
        #$this->assertArrayHasKey($this->help->public_nodeName, $results['/unit/:test']);
    }

}