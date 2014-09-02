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

use Apix\Plugin\PluginAbstract,
    Apix\TestCase;

class PluginAbstractTest extends TestCase
{

    protected $plugin;

    public function setUp()
    {
        $this->plugin =
            $this->getMockForAbstractClass('Apix\Plugin\PluginAbstract');
    }

    protected function tearDown()
    {
        unset($this->plugin);
    }

    public function testSetOptions()
    {
        $defaults = array('foo' => 'bar');
        $this->plugin->setOptions($defaults);
        $this->assertSame(
            $defaults,
            $this->plugin->getOptions()
        );

        $options = array('adapter' => 'foobar');
        $this->plugin->setOptions($options);

        $this->assertSame(
            $options+$defaults,
            $this->plugin->getOptions()
        );
    }

    public function testSetAdapterWithClosure()
    {
        $this->plugin->setAdapter(function(){return 'foo';});
        $this->assertSame(
            'foo',
            $this->plugin->getAdapter()
        );
    }

    public function testSetAdapterWithAClassName()
    {
        $this->plugin->setAdapter('Apix\Fixtures\PluginMock');
        $this->assertInstanceOf(
            'Apix\Fixtures\PluginMock',
            $this->plugin->getAdapter()
        );
    }

    public function testSetAdapterWithAnObject()
    {
        $this->plugin->setAdapter($this->plugin);
        $this->assertSame(
            $this->plugin,
            $this->plugin->getAdapter()
        );
    }

    public function testCheckAdapterClass()
    {
        $this->plugin->setAdapter('Apix\Fixtures\PluginMock');

        $this->assertTrue(
            PluginAbstract::checkAdapterClass(
                $this->plugin->getAdapter(),
                'Apix\Plugin\PluginAbstract'
            )
        );
    }

    /**
     * @expectedException   \RuntimeException
     */
    public function testCheckAdapterClassThrowsRuntimeException()
    {
        $this->plugin->setAdapter(new \stdClass);
        PluginAbstract::checkAdapterClass(
            $this->plugin->getAdapter(),
            'Apix\Plugin\PluginAbstract'
        );
    }

    public function testConstructor()
    {
        $obj = new \stdClass;
        $this->plugin->__construct($obj);

        $this->assertSame(
            array('adapter' => $obj),
            $this->plugin->getOptions()
        );
    }

}
