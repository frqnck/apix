<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix;

use Apix\Server,
    Apix\TestCase;

class HelpTest extends TestCase
{

    protected $request, $api, $help;

    protected function setUp()
    {
        $this->request = $this->getMock('Apix\HttpRequest');

        $this->api = new Server(false, $this->request);

        $this->api->onCreate('/create/:test',
            /**
             * Creates something...
             *
             * @return array  The array to return to the client
             */
            function ($test) {
                return array("You just created $test.");
            }
        );

        $this->api->onRead('/unit/:test',
            /**
             * Read/retrieves something...
             *
             * @return array  The array to return to the client
             */
            function ($test) {
                return array("You just retrieve $test.");
            }
        );

        $this->api->onModify('/unit/:test',
            /**
             * Modifies that something...
             *
             * @return array  The array to return to the client
             */
            function ($test) {
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
        unset($this->request, $this->api, $this->help);
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

    /**
     * @group tt
     */
    public function testOnReadReturnsArrayForOneEntity()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will(
                $this->onConsecutiveCalls('/unit/:test')
            );

        $results = $this->help->onRead($this->api);

        $this->assertArrayHasKey('title', $results);
        $this->assertArrayHasKey('description', $results);
        $this->assertArrayHasKey('return', $results);
        $this->assertArrayHasKey('method', $results);

        $this->assertArrayHasKey('path', $results, 'Should have a path field.');
        $this->assertSame('/unit/:test', $results['path']);

        $this->assertEquals(5, count($results));
    }

    protected function genericTest($items)
    {
        $item = &$items[0];
        $this->assertSame('/create/:test', $item['path']);
        $this->assertArrayHasKey('POST', $item['methods']);
        $this->assertEquals(1, count($item['methods']));

        $item = &$items[1];
        $this->assertSame('/unit/:test', $item['path']);
        $this->assertArrayHasKey('GET', $item['methods']);
        $this->assertArrayHasKey('PATCH', $item['methods']);
        $this->assertEquals(2, count($item['methods']));
    }

    public function testOnReadReturnsArrayForAllEntities()
    {
        $this->request->expects($this->any())
            ->method('getUri')
            ->will( $this->onConsecutiveCalls('/') );

        $results = $this->help->onRead($this->api);

        $this->assertTrue( count($results['items'])>1 );
        $this->genericTest( $results['items'] );
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
