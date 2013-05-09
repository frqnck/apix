<?php
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
        $plugin = $this->getMockForAbstractClass('Apix\Plugin\PluginAbstract');

        $obj = new \stdClass;
        $plugin->__construct($obj);

        $this->assertSame(
            array('adapter' => $obj),
            $plugin->getOptions()
        );
    }

}
