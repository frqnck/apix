<?php

namespace Zenya\Api;

#use Pimple;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetConfig()
    {
        $this->config = new Config;
        $this->assertSame(array(), $this->config->getConfig('resources'));
        $this->assertArrayHasKey('resources_default', $this->config->getConfig());
        $this->config->getConfig('non-existant');
    }

    public function testGetResources()
    {
        $this->config = new Config;
        $this->assertSame($this->config->getConfig('resources_default'), $this->config->getResources());

        $this->config = new Config(array('resources'=>array('resourceName'=>array())));
        $this->assertSame($this->config->getConfig('resources')+$this->config->getConfig('resources_default'), $this->config->getResources());
    }

    // public function setUp()
    // {
    //     if (!class_exists('Pimple')) {
    //         $this->markTestSkipped('Pimple is not available');
    //     }
    // }

    // public function testHas()
    // {
    //     $provider = new PimpleProvider(new \Pimple(), array('first' => 'first', 'second' => 'dummy'));
    //     $this->assertTrue($provider->has('first'));
    //     $this->assertTrue($provider->has('second'));
    //     $this->assertFalse($provider->has('third'));
    // }

    // public function testGetExistentProvider()
    // {
    //     $pimple = new \Pimple();
    //     $menu = $this->getMock('Knp\Menu\ItemInterface');
    //     $pimple['menu'] = function() use ($menu) {
    //         return $menu;
    //     };
    //     $provider = new PimpleProvider($pimple, array('default' => 'menu'));
    //     $this->assertSame($menu, $provider->get('default'));
    // }

    // /**
    //  * @expectedException InvalidArgumentException
    //  */
    // public function testGetNonExistentProvider()
    // {
    //     $provider = new PimpleProvider(new \Pimple());
    //     $provider->get('non-existent');
    // }

}