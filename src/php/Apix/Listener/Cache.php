<?php
namespace Apix\Listener;

class Cache extends AbstractListenerEntity
{
    protected $annotation = 'api_cache';

    protected $options = array(
        'enable'    => true,      // wether to enable caching at all
        'ttl'       => '10mins',  // null stands forever
        'flush'     => true,      // wether to flush tags at runtime (cron job?)
        'tags'      => array(),   // tags to append everytime time e.g. v1, dev
    );

    /**
     * Constructor.
     *
     * @param Cache\Adapter $adapter
     * @param array $options Array of options.
     */
    public function __construct(Cache\Adapter $adapter, array $options=null)
    {
        $this->adapter = $adapter;

        if(null !== $options) {
            $this->options = $options+$this->options;
        }
   }

    /**
     * Flush the tags explicitly
     *
     * @param boolean $enable Wether to flush or not.
     */
    public function flushAnnotatedTags($enable)
    {
        if ( $enable && $tags = $this->getSubTagValues('flush') ) {
            $this->adapter->clean($tags);
            $this->log('Tags purged', implode(', ', $tags));
        }
    }

    public function update(\SplSubject $entity)
    {
        // skip if null
        if (
            false === $this->options['enable']
            || null === $entity->getAnnotationValue($this->annotation)){
            return false;
        }
        $this->entity = $entity;

        $this->flushAnnotatedTags( $this->options['flush'] );

        // the cache id is simply the entity route name for now! todo
        $id = $entity->getRoute()->getPath();

        // Use cache
        if ($cache = $this->adapter->load($id)) {
            $this->log('loading', $id);
            return $cache;
        }

        // retrieve the output...
        $data = call_user_func_array(
            array($entity, 'call'),
            array($entity->getRoute())
        );

        // ...and cache it.
        $ttl = $this->getSubTagValues('ttl', array($this->options['ttl']));
        $ttl = $this->getTtlInternval($ttl[0]);

        $tags = array_merge(
            $this->options['tags'],
            $this->getSubTagValues('tags', array())
        );
        $tags = array_unique($tags);

        $this->adapter->save($data, $id, $tags, $ttl);
        $this->log(sprintf('saving for %d secs', $ttl), $id . ': ' . implode(', ', $tags));
        return $data;
    }

    /**
     * Returns the time-to-live interval in seconds.
     *
     * @param string $until A date/time string. @see http://php.net/strtotime
     * @param string $from A date/time string. @see http://php.net/strtotime
     * @return integer  The TTL in seconds.
     */
    public function getTtlInternval($until=null, $from='now')
    {
        return $until==0 ? null : strtotime($until)-strtotime($from);
        // $expiryDate = new \DateTime();
        // $interval = new \DateInterval($ttl);
        // return $expiryDate->add($interval);
    }

}