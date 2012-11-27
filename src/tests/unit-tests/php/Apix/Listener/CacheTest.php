<?php
namespace Apix\Listener;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    protected $cache, $adapter;

    public function setUp()
    {
		$this->adapter = $this->getMock('Apix\Listener\Cache\Adapter');
        $this->cache = new Cache($this->adapter);
    }

    protected function tearDown()
    {
        unset($this->cache);
    }

    protected function mock($str)
    {
		$entity = $this->getMock('Apix\Entity');
        $entity->expects($this->any())
        	->method('getAnnotationValue')
            ->will($this->returnValue($str));

        $route = $this->getMock('Apix\Router');
        $entity->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue($route));

		$this->cache->entity = $entity;
	}

    public function testUpdateSkipWithoutAnnotation()
    {
        $this->mock(null);
        $this->assertFalse(
            $this->cache->update($this->cache->entity)
        );
    }

    public function testUpdateDoNotSkipWithAnnotation()
    {
        $this->mock('x');
        $this->assertNull(
            $this->cache->update($this->cache->entity)
        );
    }

    public function testUpdateSkipWhenDisable()
    {
        $cache = new Cache($this->adapter, array('enable'=>false));
        $this->assertFalse(
            $cache->update(
                $this->getMock('Apix\Entity')
            )
        );
    }

    public function testUpdateLoadFromCache()
    {
        $this->mock('x');
        $this->adapter
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue('loaded'));
        $this->assertEquals('loaded',
            $this->cache->update($this->cache->entity)
        );
    }

    public function testUpdateSaveToCache()
    {
        $this->mock('x');
        $this->adapter
            ->expects($this->once())
            ->method('save');
        $this->cache->update($this->cache->entity);
    }

    public function testFlushAnnotatedTags()
    {
        $this->mock('flush=tag1');
        $this->adapter
            ->expects($this->once())
            ->method('clean');
        $this->cache->flushAnnotatedTags(true);
        $this->cache->flushAnnotatedTags(false);
    }

    public function testGetTtlInternval()
    {
		$ttl = $this->cache->getTtlInternval('+1minute');
        $this->assertEquals('60', $ttl);

		$ttl = $this->cache->getTtlInternval('1min');
        $this->assertEquals('60', $ttl);

		$ttl = $this->cache->getTtlInternval('100sec');
        $this->assertEquals('100', $ttl);

		$ttl = $this->cache->getTtlInternval('100sec');
        $this->assertEquals('100', $ttl);
    }

    public function subtagsProvider()
    {
        return array(
            array('a=1 b=2 c=3', array('keys'=>array('a', 'b', 'c'), 'values'=>array(1, 2, 3))),
            array('z=zz', array('keys'=>array('z'), 'values'=>array('zz'))),
            array("a=1\tb=2\tc=3", array('keys'=>array('a', 'b', 'c'), 'values'=>array(1, 2, 3))),
            array("a=1\nb=2\nc=3", array('keys'=>array('a', 'b', 'c'), 'values'=>array(1, 2, 3))),

            array('a=1', array('keys'=>array('a'), 'values'=>array(1))),
            array('b=2', array('keys'=>array('b'), 'values'=>array(2))),
        );
    }

    /**
     * @dataProvider subtagsProvider
     */
    public function testExtractSubTags($annotation, $expected)
    {
    	$this->mock($annotation);
        $tags = $this->cache->extractSubTags();
        $tags = array(
        	'keys'=>$tags['keys'],
        	'values'=>$tags['values']
		);
        $this->assertEquals($expected, $tags);
    }

    public function testGetSubTagValues()
    {
    	$this->mock('a=1 b=2,3,4 c=5,6,7');
 		$results = array(
 			array('k'=>'a', 'exp'=>array(1)),
 			array('k'=>'b', 'exp'=>array(2,3,4)),
 			array('k'=>'c', 'exp'=>array(5,6,7)),
 		);
 		foreach($results as $result) {
	        $values = $this->cache->getSubTagValues($result['k'], null, true);
	        $this->assertEquals($result['exp'], $values);
 		}
    }

    public function testLog()
    {
    }

}