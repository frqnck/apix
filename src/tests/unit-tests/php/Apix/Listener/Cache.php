<?php
namespace Apix\Listener;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    protected $cache;

    public function setUp()
    {
		$adapter = $this->getMock('Apix\Listener\Cache\Adapter');
        $this->cache = new Cache($adapter);
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
		
		$this->cache->entity = $entity;
	}

    public function subtagsProvider()
    {
        return array(
             array('a=a b=b c=c', array('key'=>array('a', 'b', 'c'), 'value'=>array('a', 'b', 'c'))),
             array('z=zz', array('key'=>array('z'), 'value'=>array('zz'))),
			# array("tags=tag1,tag2,tag3\nclean=tag9,tag10", '/123'),
        );
    }

    /**
     * @dataProvider subtagsProvider
     */
    public function testExtractTags($annotation, $expected, $fresh=true)
    {
    	$this->mock($annotation);
        $tags = $this->cache->extractTags($fresh);
        $tags = array(
        	'key'=>$tags['key'],
        	'value'=>$tags['value']
        	);
        $this->assertEquals($expected, $tags);
    }

}