<?php
namespace Apix\Plugins;

use Apix\Entity;

class Cache extends PluginAbstractEntity
{

    public static $hook = array('entity', 'early');

    protected $annotation = 'api_cache';

    protected $options = array(
        'adapter'    => 'Apix\Plugins\Cache\Adapter',
        'enable'     => true,              // wether to enable or not
        'ttl'        => '10mins',          // set the TTL, null stands forever
        'flush'      => true,              // flush tags at runtime (cronjob)
        'tags'       => array(),           // default tags to append (v1, dev)
        'prefix_key' => 'apix-cache-key:', // prefix cache keys
        'prefix_tag' => 'apix-cache-tag:', // prefix cache tags
    );

    /**
     * @{@inheritdoc}
     */
    public function update(\SplSubject $entity)
    {
        // skip if null
        if (
            false === $this->options['enable']
            || null === $entity->getAnnotationValue($this->annotation)){
            return false;
        }

        $this->setEntity($entity);

        try {
            $this->flushAnnotatedTags($this->options['flush']);

            // the cache id is simply the entity route name for now!
            $id = $entity->getRoute()->getPath();

            // use the cache if present
            if ($cache = $this->adapter->load($id)) {
                $this->log('loading', $id);

                return $cache;
            }

            // else call and retrieve the method's output...
            $data = call_user_func_array(
                array($entity, 'call'),
                array($entity->getRoute())
            );

            // ...and cache it for later usage.
            $ttl = $this->getSubTagValues('ttl', array($this->options['ttl']));
            $sec = $this->getTtlInternval($ttl[0]);

            $tags = array_merge(
                $this->options['tags'],
                $this->getSubTagValues('tags', array())
            );
            $tags = array_unique($tags);

            $this->adapter->save($data, $id, $tags, $sec);
            $this->log(
                sprintf('saving for %ds', $sec, $ttl[0]),
                $id . ' -- ' . implode(', ', $tags)
            );

        } catch (\Exception $e) {
            $this->log('error', $e->getMessage(), 'ERROR');
        }

        return $data;
    }

    /**
     * Flush the tags explicitly
     *
     * @param boolean $enable Wether to flush or not
     */
    public function flushAnnotatedTags($enable)
    {
        if ( $enable && $tags = $this->getSubTagValues('flush') ) {
            $this->adapter->clean($tags);
            $this->log('Tags purged', implode(', ', $tags));
        }
    }

    /**
     * Returns the time-to-live interval in seconds.
     * Inputs strings are date/time strtotime formatted.
     * @see http://php.net/strtotime
     *
     * @param  string  $until
     * @param  string  $from
     * @return integer The TTL in seconds.
     */
    public function getTtlInternval($until=null, $from='now')
    {
        return $until == 0 ? null : strtotime($until)-strtotime($from);
    }

}
