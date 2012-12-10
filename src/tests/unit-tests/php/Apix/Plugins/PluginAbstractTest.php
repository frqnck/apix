<?php
namespace Apix\Plugins;

use Apix\Plugins\PluginAbstract,
    Apix\TestCase;

class PluginAbstractTest extends TestCase
{

    protected $plugin;

    public function setUp()
    {
        $this->plugin =
            $this->getMockForAbstractClass('Apix\Plugins\PluginAbstract');
    }

    protected function tearDown()
    {
        unset($this->plugin);
    }

    public function testSetOptions()
    {
        $defaults = array('foo'=>'bar');
        $this->plugin->setOptions($defaults);
        $this->assertSame(
            $defaults,
            $this->plugin->getOptions()
        );

        $options = array('adapter'=>'foobar');
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

    /**
     * @TODO
     * @ expectedException           \RuntimeException
     */
    // public function testSetAdapterThrowRuntimeException()
    // {
    //     //$this->plugin->setAdapter('Apix\Plugins\PluginAbstract');
    //     // $this->assertSame(
    //     //     $this->plugin,
    //     //     $this->plugin->getAdapter()
    //     // );

    //     $this->plugin->setAdapter(new \stdClass);
    // }

    public function testConstructor()
    {
        $plugin = $this->getMockForAbstractClass('Apix\Plugins\PluginAbstract');

        $obj = new \stdClass;
        $plugin->__construct($obj);

        $this->assertSame(
            array('adapter' => $obj),
            $plugin->getOptions()
        );
    }

}
