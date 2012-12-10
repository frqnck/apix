<?php
namespace Apix\Plugins;

use Apix\Entity;

class Cache extends PluginAbstractEntity
{

    public static $hook = array(
        'entity',
        'early',
        'interface' => 'Apix\Plugins\Cache\Adapter'
    );

    protected $annotation = 'api_cache';

    protected $options = array(
        'enable'     => true,                     // wether to enable or not
        'adapter'    => 'Apix\Plugins\Cache\Apc', // instantiate by default
        'ttl'        => '10mins',          // the lifetime, null stands forever
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
                $this->log('loading', $id, 'DEBUG');

                return $cache;
            }

            // else call and retrieve the method's output...
            $data = call_user_func_array(
                array($entity, 'call'),
                array($entity->getRoute())
            );

            // ...and cache it for later usage.
            $ttl = $this->getSubTagValues('ttl', array($this->options['ttl']));
            $sec = self::timeInternval($ttl[0]);

            $tags = array_merge(
                $this->options['tags'],
                $this->getSubTagValues('tags', array())
            );
            $tags = array_unique($tags);

            $this->adapter->save($data, $id, $tags, $sec);
            $this->log(
                sprintf('saved for %ds', $sec, $ttl[0], 'DEBUG'),
                $id . ' -- ' . implode(', ', $tags)
            );

        } catch (\Exception $e) {
            // $l = new Log();
            // $l->logd('errro');

            #$l->log('error', $e->getMessage(), 'ERROR');
            $this->log('error', $e->getMessage(), 'ERROR');
            $data = isset($data) ? $data : 'temp-execption';
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
            $this->log('Tags purged', implode(', ', $tags), 'DEBUG');
        }
    }

    /**
     * Returns the time interval in seconds.
     *
     * Inputs strings are date/time strtotime formatted.
     * @see http://php.net/strtotime
     *
     * @param  string  $end   The end time.
     * @param  string  $start The start time, default to 'now'.
     * @return integer The interval in seconds.
     */
    public static function timeInternval($end=null, $start='now')
    {
        return 0 == $end ? null : strtotime($end)-strtotime($start);
    }

}
