<?php
namespace Apix;

use Apix\Server,
    Apix\TestCase;

class HelpTest extends TestCase
{

    protected $request, $api, $help;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Apix\HttpRequest')
                              ->disableOriginalConstructor()
                              ->getMock();

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

        $this->api->setRouting(
            $this->request,
            $this->api->resources->toArray()
        );

        $this->help = new Resource\Help($this->api);
    }

    protected function tearDown()
    {
        unset($this->request);
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

        #$this->help->onRead($this->api);
        #$this->assertSame('/*', $this->api->getRoute()->getName());
    }

    public function testOnReadReturnsArrayForOneEntity()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will(
                $this->onConsecutiveCalls('/unit/:test')
            );

        $results = $this->help->onRead($this->api);

        $this->assertArrayHasKey('path', $results);

        $this->assertEquals(4, count($results));
        $this->assertArrayHasKey('GET', $results['methods']);
        $this->assertArrayHasKey('PATCH', $results['methods']);
    }

    protected function genericTest($items)
    {
        #print_r($items);exit;
        $this->assertSame('/create/:test', $items[2]['path']);
        #$this->assertArrayHasKey('POST', $items[2]['methods']);

        $this->assertSame('/unit/:test', $items[3]['path']);
        #$this->assertArrayHasKey('GET', $items[3]['methods']);
        #$this->assertArrayHasKey('PATCH', $items[3]['methods']);
    }

    public function testOnReadReturnsArrayForAllEntities()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/') );

        $results = $this->help->onRead($this->api);

        $this->assertTrue( count($results['items'])>1 );
        $this->genericTest($results['items']);
    }

    public function testOnHelpRetrieveAllTheEntities()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/*') );

        $results = $this->help->onHelp($this->api);

        $this->genericTest($results['items']);
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
