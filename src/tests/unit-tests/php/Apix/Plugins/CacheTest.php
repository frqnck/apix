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

namespace Apix\Plugin;

use Apix\HttpRequest,
    Apix\Response,
    Apix\TestCase,
    Apix\Service;

class CacheTest extends TestCase
{
    protected $plugin, $request, $response, $opts;

    public function setUp()
    {
        $this->request = new HttpRequest();
        $this->response = new Response($this->request);
        $this->response->unit_test = true;

        Service::set('response', $this->response);

        $this->entity = $this->getMock('Apix\Entity');

        $this->plugin = new Cache( array('enable' => true, 'adapter' => 'Apix\Cache\Runtime') );

        $this->opts = $this->plugin->getOptions();
    }

    protected function tearDown()
    {
        unset($this->plugin, $this->request, $this->response, $this->opts);
    }

    public function testIsDisable()
    {
        $this->opts['enable'] = false;
        $plugin = new Cache( $this->opts );
        $this->assertFalse( $plugin->update( $this->entity ) );
    }

    public function testFlushAnnotatedTagsReturnsNull()
    {
        $this->plugin->update( $this->entity );

        $this->assertNull( $this->plugin->flushAnnotatedTags(false) );
        $this->assertNull( $this->plugin->flushAnnotatedTags(true) );
    }

    public function testFlushAnnotatedTagsReturnFalse()
    {
        $this->plugin->update( $this->entity );
        $tags = array('tag1');

        $this->assertFalse( $this->plugin->flushAnnotatedTags(true, $tags) );
    }

    public function testFlushAnnotatedTagsReturnTrue()
    {
        $this->plugin->update( $this->entity );
        $tags = array('tag1');

        // save the tags and flush them.
        $this->plugin->getAdapter()->save('foo value', 'foo', $tags);
        $this->assertTrue( $this->plugin->flushAnnotatedTags(true, $tags) );
    }

    public function testReturnFreshData()
    {
        $val = 'fresh method value';
        $this->entity->expects($this->any())
                ->method('call')
                ->will($this->returnValue($val));

        $data = $this->plugin->update($this->entity);
        $this->assertSame($val, $data);
    }

    public function testReturnCachedData()
    {
        $this->plugin->getAdapter()->save('foo value from cache', '/');

        $data = $this->plugin->update($this->entity);
        $this->assertSame('foo value from cache', $data);
    }

    /**
     * @expectedException           \Exception
     * @expectedExceptionMessage    blah blah
     */
    public function testReThrowsException()
    {
        $this->entity->expects($this->once())
                ->method('call')
                ->will($this->throwException(new \Exception('blah blah')));
        $data = $this->plugin->update($this->entity);
    }

}
