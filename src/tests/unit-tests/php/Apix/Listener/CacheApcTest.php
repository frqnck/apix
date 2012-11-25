<?php
namespace Apix\Listener\Cache;

class CacheApcTest extends \PHPUnit_Framework_TestCase
{

    protected $cache;

    public function setUp()
    {
        if(!extension_loaded('apc')) {
            $this->markTestSkipped('Extension APC not installed');
        }

        if(!ini_get('apc.enable_cli')) {
            $this->markTestSkipped('Extension APC not enable for cli');
        }

        $this->cache = new Apc;
    }

    protected function tearDown()
    {
        apc_clear_cache('user');
        unset($this->cache);
    }

    public function testLoadReturnsNullWhenEmpty()
    {
        $this->assertNull(
            $this->cache->load('id')
        );

    }

    public function testSaveAndLoad()
    {
        $this->assertTrue(
            $this->cache->save('strData', 'id')
        );

        $this->assertEquals(
            'strData', 
            $this->cache->load('id')
        );
    }

    public function testSaveAndLoadWithTags()
    {
        $this->assertTrue(
            $this->cache->save('strData', 'id', array('tag1', 'tag2'))
        );

        $tag = $this->cache->mapTag('tag2');
        $ids = apc_fetch($tag);

        $this->assertEquals(
            array('[apixKey] id'), 
            $ids
        );

        $this->assertSame(
            'strData',
            $this->cache->load('id')
        );
    }

    public function testSaveAndLoadWithTtl()
    {
        $this->assertTrue(
            $this->cache->save('ttl-1', 'id', null, 1)
        );

        $key = $this->cache->mapKey('id');
 
        print_r(
            $this->cache->expire($key)
        );

        $this->assertNull(
            // apc_fetch($key)
            $this->cache->load('id')
        );
    }

}