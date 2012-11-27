<?php
namespace Apix\Listener\Cache;

class Redis extends AbstractCache
{

    /**
     * Constructor.
     */
    public function __construct(\Redis $redis, array $options=array())
    {
        $options['atomicity'] = !isset($options['atomicity'])
                                || true === $options['atomicity']
 								? \Redis::MULTI
 								: \Redis::PIPELINE;

        // $options['flush_all'] = isset($options['flush_all'])
        //                         && true === $options['flush_all']
        //                         ? true
        //                         : false;

        parent::__construct($redis, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function load($key, $type='key')
    {
        if( $type == 'tag' ){
            $cache = $this->adapter->sMembers(
                $this->mapTag($key)
            );
            return empty($cache) ? null : $cache;
        }
        $cache = $this->adapter->get(
            $this->mapKey($key)
        );
        return false === $cache ? null : $cache;
	}

    /**
     * {@inheritdoc}
     */
    public function save($data, $key, array $tags=null, $ttl=null)
    {
        $key = $this->mapKey($key);

        if(null === $ttl || 0 === $ttl) {
            $success = $this->adapter->set($key, $data);
        } else {
            $success = $this->adapter->setex($key, $ttl, $data);
        }

		if($success && !empty($tags)) {
			$redis = $this->adapter->multi($this->options['atomicity']);
			foreach ($tags as $tag) {
				$redis->sAdd($this->mapTag($tag), $key);
			}
			$redis->exec();
		}

		return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function clean(array $tags)
    {
        #$items = array();
		foreach($tags as $tag) {
            $keys = $this->load($tag, 'tag');
            array_walk_recursive(
                $keys,
                function($key) use (&$items) { $items[] = $key; }
            );
            $items[] = $this->mapTag($tag);
		}

    	return $this->adapter->del($items) ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
		$key = $this->mapKey($key);

        $tags = $this->adapter->keys($this->mapTag('*'));
        if(!empty($tags)) {
            $redis = $this->adapter->multi($this->options['atomicity']);
            foreach ($tags as $tag) {
                $redis->sRem($tag, $key);
            }
            $redis->exec();
        }

        return $this->adapter->del($key) ? true : false;
    }

    /**
     * Flush all cached items.
     */
    public function flush($all=false)
    {
        if(true === $all) {
            return $this->adapter->flushAll();
        }
        $items = array_merge(
            $this->adapter->keys($this->mapTag('*')),
            $this->adapter->keys($this->mapKey('*'))
        );
        return $this->adapter->del($items) ? true : false;
    }

    /**
     * Returns some internal informations about an cached item.
     *
     * @return array|false
     */
    public function getInternal($key)
    {
        return false;
    }

}