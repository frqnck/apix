<?php
namespace Apix\Listener\Cache;

class Apc implements Adapter
{

    protected $key_prefix;
    protected $tag_prefix;

    /**
     * Constructor.
     *
     * @param string $key_prefix Prefix APC keys.
     * @param string $tag_prefix Prefix APC keys to simulate tags.
     */
    public function __construct($key_prefix='[apixKey] ', $tag_prefix='[apixTag] ')
    {
        $this->key_prefix = $key_prefix;
        $this->tag_prefix = $tag_prefix;
    }

    /**
     * Returns a prefixed and sanitased cache id.
     *
     * @param  string $id The cache Id.
     * @return string
     */
    public function mapKey($id)
    {
        return $this->sanitise($this->key_prefix . $id);
    }

    /**
     * Returns a prefixed and sanitased cache tag.
     *
     * @param  string $tag The cache tag.
     * @return string
     */
    public function mapTag($tag)
    {
        return $this->sanitise($this->tag_prefix . $tag);
    }

    /**
     * Returns a sanitased string for id/tagging purpose.
     *
     * @param  string $id The string to sanitise.
     * @return string
     */
    public function sanitise($id)
    {
        return $id; // Probably not required anymore!
        // return str_replace(array('/', '\\', ' '), '_', $id);
    }

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
    public function save($data, $id, array $tags=null, $ttl=false)
    {
        $id = $this->mapKey($id);
        $store = array();
        // APC does not natively support tags so lets simulate.
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
                    print_r($ids);
                    foreach($ids as $id) {
                        apc_delete($id);
                    }
                    apc_delete($tag);
                }
            }
        }
    }

}