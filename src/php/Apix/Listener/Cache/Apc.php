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
    public function save($data, $id, array $tags=null, $ttl=0)
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
        return apc_store($store, null, $ttl);
    }

    /**
     * {@inheritdoc}
     */
    public function clean(array $tags=null)
    {
        // APC does not natively support tags so lets simulate.
        if(!empty($tags)) {
            foreach($tags as $tag) {
                $tag = $this->mapTag($tag);
                $ids = apc_fetch($tag, $success);
                if($success) {
                    foreach($ids as $id) {
                        apc_delete($id);
                    }
                    apc_delete($tag);
                }
            }
        }
    }

}