<?php
namespace Apix\Plugins;

use Apix\TestCase;

class CacheTest extends TestCase
{

    protected $cache, $adapter;

    public function setUp()
    {
        $this->adapter = $this->getMock('Apix\Plugins\Cache\Adapter');
        $this->cache = new Cache($this->adapter);
    }

    protected function tearDown()
    {
        unset($this->cache);
        unset($this->adapter);
    }

    protected function mock($return)
    {
        $entity = $this->getMock('Apix\Entity');

        $entity->expects($this->any())
            ->method('getAnnotationValue')
            ->will($this->returnValue($return));

        $entity->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue(
                    $this->getMock('Apix\Router')
                )
            );

        $this->cache->setEntity($entity);

        return $entity;
    }

    public function testUpdateSkipWithoutAnnotation()
    {
        $this->mock(null);
        $this->assertFalse(
            $this->cache->update($this->cache->getEntity())
        );
    }

    public function testUpdateDoNotSkipWithAnnotation()
    {
        $this->mock('x');
        $this->assertNull(
            $this->cache->update($this->cache->getEntity())
        );
    }

    public function testItWillSkipWhenDisable()
    {
        $cache = new Cache($this->adapter, array('enable'=>false));
        $this->assertFalse(
            $cache->update(
                $this->getMock('Apix\Entity')
            )
        );
    }

    public function testItWillLoadFromCache()
    {
        $this->mock('x');

        $this->adapter
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue('loaded from cache'));

        $this->assertEquals(
            'loaded from cache',
            $this->cache->update($this->cache->getEntity())
        );
    }

    public function testItWillSaveToCache()
    {
        $this->mock('x');

        $this->adapter
            ->expects($this->once())
            ->method('save');

        $this->cache->update($this->cache->getEntity());
    }

    public function testItWillFlushAnnotatedTags()
    {
        $this->mock('flush=tag1');
        $this->adapter
            ->expects($this->once())
            ->method('clean');
        $this->cache->flushAnnotatedTags(true);
        $this->cache->flushAnnotatedTags(false);
    }

    public function timingProvider()
    {
        return array(
            array('+1minute', 60),
            array('1min', 60),
            array('100sec', 100),
            array('+1minute', 60),
            array('2days', 172800),
            array('48hours', 172800),
            array('1week', 604800)
        );
    }

    /**
     * @dataProvider timingProvider
     */
    public function testTimeInternval($timeString, $seconds)
    {
        $this->assertEquals(
            $seconds,
            Cache::timeInternval($timeString)
        );
    }

    public function subtagsProvider()
    {
        return array(
            array(
                'a=1 b=2 c=3',
                array('keys'=>array('a', 'b', 'c'), 'values'=>array(1, 2, 3))
            ),
            array('z=zz', array('keys'=>array('z'), 'values'=>array('zz'))),
            array(
                "a=1\tb=2\tc=3",
                array('keys'=>array('a', 'b', 'c'), 'values'=>array(1, 2, 3))
            ),
            array(
                "a=1\nb=2\nc=3",
                array('keys'=>array('a', 'b', 'c'), 'values'=>array(1, 2, 3))
            ),
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
            'keys'   => $tags['keys'],
            'values' => $tags['values']
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
         foreach ($results as $result) {
            $values = $this->cache->getSubTagValues($result['k'], null, true);
            $this->assertEquals($result['exp'], $values);
         }
    }

    public function OFFtestLogException()
    {
        $this->adapter
            ->expects($this->once())
            ->method('save')
            ->will(
                $this->throwException(new \Exception('exception'))
            );

        $this->cache = new Cache($this->adapter);

        $this->mock('x');

        $log= $this->getMock('Apix\Plugins\Log', array('logd'));
        $log->expects($this->once())
            ->method('logd')
            ->will($this->returnArgument(0));
            // ->with(
            //     $this->equalTo('error'),
            //     $this->anything(),
            //     $this->equalTo('ERROR')
            // );

        $this->assertEquals(
            'temp-execption',
            $this->cache->update($this->cache->getEntity())
        );
    }

}
