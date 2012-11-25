<?php
namespace Apix\Listener\Cache;

class Apc extends AbstractCache
{

    /**
     * {@inheritdoc}
     */
    public function load($id)
    {
        $cached = apc_fetch($this->mapKey($id), $success);

        return false === $success ? null : $cached;
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $id, array $tags=null, $ttl=null)
    {
        $id = $this->mapKey($id);
        $store = array();
        // APC does not natively support tags so lets simulate, shall we.
        if(!empty($tags)) {
            foreach($tags as $tag) {
                $tag = $this->mapTag($tag);
                $ids = apc_fetch($tag, $success);
                if(false === $success) {
                    $store[$tag] = array($id);
                } else {
                    $ids[] = $id;
                    $store[$tag] = array_unique($ids);
                }
            }
        }
        $store[$id] = $data;

        return !in_array(false, apc_store($store, null, $ttl));
    }

    /**
     * {@inheritdoc}
     */
    public function clean(array $tags=null)
    {
        if(!empty($tags)) {
            $rmed = array();
            // APC does not natively support tags so lets simulate.
            foreach($tags as $tag) {
                $tag = $this->mapTag($tag);
                $ids = apc_fetch($tag, $success);
                if($success) {
                    foreach($ids as $id) {
                        $rmed[] = apc_delete($id);
                    }
                    $rmed[] = apc_delete($tag);
                }
            }
            return in_array(false, $rmed);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $id = $this->mapKey($id);

        return apc_delete($id);
    }


    function expire($key)
    {
        $caches = apc_cache_info('user');
        if (!empty($caches['cache_list'])) {
            foreach ($caches['cache_list'] as $cache) {
                if ($cache['info'] != $key)
                    continue;

                return $cache['ttl'] == 0
                        ? 0
                        : $cache['creation_time']+$cache['ttl'];
            }
        }

        return false;
    }

}