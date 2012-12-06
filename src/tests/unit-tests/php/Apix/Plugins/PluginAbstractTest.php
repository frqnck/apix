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
        $options = array('adapter'=>function(){return 'foo';});
        $this->plugin->setAdapter($options);
        $this->assertSame(
            'foo',
            $this->plugin->getAdapter()
        );
    }

    public function testSetAdapterWithAbstractClass()
    {
        $options = array('adapter' => 'Apix\Plugins\PluginAbstract');
        $this->plugin->setOptions($options);

        $options = array('adapter'=>$this->plugin);
        $this->plugin->setAdapter($options);
        $this->assertSame(
            $this->plugin,
            $this->plugin->getAdapter()
        );
    }

    /**
     * @expectedException           \RuntimeException
     */
    public function testSetAdapterWithAbstractClassThrowRuntimeException()
    {
        $options = array('adapter' => 'Apix\Plugins\PluginAbstract');
        $this->plugin->setOptions($options);

        $options = array('adapter' => new \stdClass);
        $this->plugin->setAdapter($options);
    }

    public function testConstructor()
    {
        $plugin = $this->getMockForAbstractClass('Apix\Plugins\PluginAbstract');

        $obj = new \stdClass;
        $plugin->__construct($obj);

        $this->assertSame(
            array('adapter'=>$obj),
            $plugin->getOptions()
        );
    }

}
