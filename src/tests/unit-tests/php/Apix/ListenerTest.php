<?php

namespace Apix;

use Apix\TestCase;

class ListenerTest extends TestCase
{
    protected $plugin_name = 'Apix\Fixtures\PluginMock';

    protected $listener, $plugin;

     public function setUp()
    {
        $this->listener = new Listener;
        $this->plugin = $this->getMock($this->plugin_name, array('update'));
    }

    protected function tearDown()
    {
        unset($this->plugin);
        unset($this->listener);
    }

    public function testUpdateIsCalledOnNotifyExactlyTwoTimes()
    {
        $this->plugin->expects($this->exactly(2))
                 ->method('update');

        $this->listener->attach($this->plugin);

        $this->listener->notify('first time');
        $this->listener->notify('scond time');
    }

    public function testSameWillAttachOnlyOnce()
    {
        $this->listener->attach($this->plugin);
        $this->listener->attach($this->plugin);

        $this->assertEquals(1, $this->listener->count());
    }

    public function testDifferentWillBeAccountable()
    {
        $this->listener->attach($this->plugin);

        $obs2 = $this->getMock($this->plugin_name, array('update'));
        $this->listener->attach($obs2);

        $this->assertEquals(2, $this->listener->count());
    }

    public function testDetachAndCountable()
    {
        $this->listener->attach($this->plugin);

        $obs2 = $this->getMock($this->plugin_name, array('update'));
        $this->listener->attach($obs2);

        $this->listener->detach($obs2);
        $this->assertEquals(1, $this->listener->count());
    }

    public function testGetIteratorIsEmptyArray()
    {
        $this->assertSame(array(), $this->listener->getIterator());
    }

    public function testGetIteratorOnAttach()
    {
        $this->listener->attach($this->plugin);
        $this->assertTrue(count($this->listener->getIterator()) == 1);

        $obs2 = $this->getMock($this->plugin_name);
        $this->listener->attach($obs2);
        $this->assertTrue(count($this->listener->getIterator()) == 2);
    }

    public function testGetPluginsAtLevel()
    {
        $plugins = $this->listener->getPluginsAtLevel('unexistant');
        $this->assertSame(false, $plugins);

        $foo = array('bar');
        $this->listener->setPluginsAtLevel('unit-test', $foo);

        $plugins = $this->listener->getPluginsAtLevel('unit-test');
        $this->assertSame($foo, $plugins);
    }

    public function testhook()
    {
        $plugins = array(
            'early' => array(
                $this->plugin_name,
                $this->plugin_name => array('foo', 'bar')
            )
        );
        $this->listener->setPluginsAtLevel('server', $plugins);

        $this->listener->hook('server', 'early');
        $this->assertSame(2, $this->listener->count());

        $this->listener->hook('server', 'early');
        $this->assertSame(4, $this->listener->count());
    }

    /**
     * @expectedException   \RuntimeException
     */
    public function testLoadPluginThrowsRuntimeException()
    {
        $plugin = 'class-that-do-not-exist';
        $this->listener->loadPlugin(0, $plugin);
    }

    public function testLoadPlugin()
    {
        $plug = $this->plugin_name;
        $this->assertTrue($this->listener->loadPlugin(0, $plug));

        $this->assertSame(
            array('type'=>array($this->plugin_name)),
            $this->listener->getPluginsAtLevel($plug::$hook[0])
        );
    }

    public function testLoadsPlugin()
    {
        $plugins = array(
            $this->plugin_name, $this->plugin_name
        );
        $this->listener->loadPlugins($plugins);

        $this->assertSame(
            array('type' => $plugins),
            $this->listener->getPluginsAtLevel($plugins[0]::$hook[0])
        );
    }

    /**
     * @expectedException   \DomainException
     */
    public function testLoadPluginThrowsDomainExceptionWhenNoHookSet()
    {
        $r = new \ReflectionClass($this->plugin_name);
        $r->setStaticPropertyValue('hook', null);
        $this->listener->loadPlugin(0, $this->plugin_name);
    }

}
