<?php
namespace Apix\Listener\Cache;

class Redis extends AbstractCache
{

    /**
     * Constructor.
     */
    public function __construct(\Redis $redis, array $options=array())
    {
 		$options['atomicity'] = true === $options['atomicity']
 								? Redis::MULTI
 								: Redis::PIPELINE; 

        parent::__construct($redis, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $cache = $this->adapter->get($this->mapKey($id));

        return false === $cache ? null : $cache;
	}

    /**
     * {@inheritdoc}
     */
    public function save($data, $id, array $tags=null, $ttl=0)
    {
        $id = $this->mapKey($id);

		$bool = $redis->setex($id, $ttl, $data);
		if(!empty($tags) && $bool) {
			$redis = $this->adapter->multi($this->options['atomicity']);

			foreach ($tags as $tag) {
				$redis->sAdd($this->mapTag($tag), $id);
			}
			$redis->exec();
		}
		
		return $bool;
    }

    /**
     * {@inheritdoc}
     */
    public function clean(array $tags=null)
    {
        if(!empty($tags)) {
			$redis = $this->adapter->multi($this->options['atomicity']);

			foreach($tags as $tag) {
				$tag = $this->mapTag($tag);
				
				$items = $this->adapter->sMembers($tag);
				foreach ($items as $item) {
					$redis->del($item);
				}
				$redis->del($tag);
			}
			$redis->exec();
    	}
    }

} 