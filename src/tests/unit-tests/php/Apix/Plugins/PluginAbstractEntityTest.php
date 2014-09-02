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

use Apix\Plugin\PluginAbstractEntity,
    Apix\TestCase;

class PluginAbstractEntityTest extends TestCase
{

    protected $entity, $plugin;

    public function setUp()
    {
        $this->plugin =
            $this->getMockForAbstractClass('Apix\Plugin\PluginAbstractEntity');

        $this->entity = $this->getMockBuilder('Apix\Entity')
                                ->disableOriginalConstructor()
                                ->getMock();
        $this->plugin->setEntity($this->entity);

        $this->entity
            ->expects($this->any())
            ->method('getAnnotationValue')
            // ->with($this->equalTo('anno1'))
            ->will($this->returnValue('foo=yes bar=val1,val2'));
        // $this->plugin->setAnnotation('anno1');
    }

    protected function tearDown()
    {
        unset($this->entity, $this->plugin);
    }

    public function testExtractSubTags()
    {
        $subtags = $this->plugin->extractSubTags();

        $this->assertArrayHasKey('keys', $subtags);
        $this->assertArrayHasKey('values', $subtags);

        $this->assertSame(array('foo', 'bar'), $subtags['keys']);
        $this->assertSame(array('yes', 'val1,val2'), $subtags['values']);
    }

    public function testGetSubTagValues()
    {
        $this->assertSame(
            array('yes'), $this->plugin->getSubTagValues('foo')
        );
        $this->assertSame(
            array('val1', 'val2'), $this->plugin->getSubTagValues('bar')
        );
        $this->assertSame(
            array('default'),
            $this->plugin->getSubTagValues('none', array('default'))
        );
    }

    public function testGetSubTagBool()
    {
        $this->assertTrue( $this->plugin->getSubTagBool('foo') );
        $this->assertFalse( $this->plugin->getSubTagBool('bar') );
        $this->assertNull( $this->plugin->getSubTagBool('nil') );
    }

    public function testGetSubTagBoolString()
    {
        // $this->plugin->setAnnotation('anno2');
        $this->entity = $this->getMockBuilder('Apix\Entity')
                                ->disableOriginalConstructor()
                                ->getMock();

        $this->entity
            ->expects( $this->any() )
            ->method('getAnnotationValue')
            // ->with($this->equalTo('anno2'))
            ->will($this->returnValue(
                'True=true False=false Yes=yes No=no Zero=0 One=1')
            );

        $this->plugin->setEntity($this->entity);

        $this->assertTrue( $this->plugin->getSubTagBool('True') );
        $this->assertFalse( $this->plugin->getSubTagBool('False') );
        $this->assertTrue( $this->plugin->getSubTagBool('Yes') );
        $this->assertFalse( $this->plugin->getSubTagBool('No') );
        $this->assertFalse( $this->plugin->getSubTagBool('Zero') );
        $this->assertTrue( $this->plugin->getSubTagBool('One') );
        $this->assertNull( $this->plugin->getSubTagBool('Nil') );
    }

}
