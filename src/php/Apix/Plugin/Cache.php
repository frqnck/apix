<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix\Plugin;

use Apix\Service;

class Cache extends PluginAbstractEntity
{

    public static $hook = array(
        'entity',
        'early',
        'interface' => 'Apix\Cache\Adapter' // 'frqnck\Cachette'
    );

    protected $annotation = 'api_cache';

    protected $options = array(
        'enable'        => true,              // wether to enable or not
        'adapter'       => 'Apix\Cache\Apc',  // instantiate by default
        'default_ttl'   => '10mins',          // lifetime, null stands forever
        'runtime_flush' => true,              // flush tags at runtime (cronjob)
        'append_tags'   => array(),           // default tags to append (v1, dev)
    );

    /**
     * @{@inheritdoc}
     */
    public function update(\SplSubject $entity)
    {
        $this->setEntity($entity);

        // skip this plugin if it is disable.
        if ( !$this->getSubTagBool('enable', $this->options['enable']) ) {
            return false;
        }
        $logger = Service::get('logger');

        try {
            $this->flushAnnotatedTags($this->options['runtime_flush']);

            // the cache id is simply the entity route name for now!
            $id = Service::get('response')->getRequest()->getRequestUri();

            // 1.) use the cache if present
            if ($data = $this->adapter->loadKey($id)) {
                $entity->setResults((array) $data);

                $logger->debug('Cache: loading resource "{id}"', array('id'=>$id));

            } else {

                // 2.) otherwise call and retrieve the method's output
                $data = call_user_func_array(array($entity, 'call'), array(true));

                $ttl = $this->getSubTagString('ttl', $this->options['default_ttl']);

                $seconds = $this->getTimeInterval($ttl);

                $tags = $this->getTags();

                // 3.) cache it for later usage
                $this->adapter->save($data, $id, $tags, $seconds);

                $logger->debug(
                    'Cache: saving resource "{id}" for {sec}s [tags: {tags}]',
                    array('id' => $id, 'sec' => $seconds, 'tags' => $tags)
                );
            }

        } catch (\Exception $e) {
            $logger->error(
                'Cache: Exception "{msg}"',
                array('msg' => $e->getMessage(), 'exception' => $e)
            );

            throw $e; // rethrow!
        }

        return $data;
    }

    /**
     * Flush the tags explicitly
     *
     * @param  boolean      $enable Wether to flush or not
     * @return boolean|null
     */
    public function flushAnnotatedTags($enable, array $default = null)
    {
        if ( $enable
            && $tags = $this->getSubTagValues('flush', $default)
        ) {
            $success = $this->adapter->clean($tags);

            $logger = Service::get('logger');
            $logger->debug('Cache: tags purged [{tags}]', array('tags' => $tags));

            return $success;
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
    protected function getTimeInterval($end=null, $start='now')
    {
        return 0 == $end ? null : strtotime($end)-strtotime($start);
    }

    /**
     * Returns all the tags for this cache.
     *
     * @return array
     */
    protected function getTags()
    {
        $tags = array_merge(
            $this->options['append_tags'],
            $this->getSubTagValues('tags', array())
        );

        return array_unique($tags);
    }

}
