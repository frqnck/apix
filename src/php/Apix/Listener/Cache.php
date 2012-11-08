<?php
namespace Apix\Listener;

class Cache implements \SplObserver
{
    protected $annotation = 'api_cache';
    protected $adapter;
    protected $options = array(
        'enable'    => true,        // Wether to enable caching at all.
        'ttl'       => '10mins',    // null stands forever.
        'flush'     => true,        // flush tags each time (e.g. cron job instead).
        'tags'      => null,        // default tag(s) to append. TODO!
    );

    /**
     * Constructor.
     *
     * @param object $adapter
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     * @throws \RuntimeException
     */
    public function __construct($adapter=null, array $options=array())
    {
        $this->options = $options+$this->options;

        switch (true) {
            case ($adapter instanceof Cache\Adapter):
                $this->adapter = $adapter;
            break;

            case ($adapter instanceof Zend_Cache):
                $this->adapter = $adapter;
            break;

            default:
              throw new \RuntimeException('Unable to open the Cache adapter');
        }
    }

    function log($msg, $ref=null)
    {
        if(defined('DEBUG')) {
            $str = sprintf('%s %s (%s)', __CLASS__, $msg, $ref);
            error_log( $str );
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

        // clean/purge tags explicitly
        if ( $this->options['flush'] && $tags = $this->getTagValues('clean') ) {
            $this->adapter->clean($tags);
            $this->log('Tags purged', implode(', ', $tags));
        }

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
        $ttl = $this->getTagValues('ttl', $this->options['ttl']);
        $ttl = $this->getTtl($ttl[0]);

        $tags = $this->getTagValues('tags');
        
        $this->adapter->save($data, $id, $tags, $ttl);
        $this->log(sprintf('saving for %d secs', $ttl), $id . ': ' . implode(', ', $tags));
        return $data;
    }

    /**
     * Returns the TTL in seconds.
     * @return integer  The TTL in seconds.
     */
    public function getTtl($until=null, $from='now')
    {
        return $until==0 ? null : strtotime($until)-strtotime($from);
        // $expiryDate = new \DateTime();
        // $interval = new \DateInterval($ttl);
        // return $expiryDate->add($interval);
    }

    /**
     * Retuns the value of the specifified subtag.
     *
     * @param  string       $key        The $key to retrieve.
     * @param  string|null  $default    The default value. 
     * @return array|null               An indexed array of values or null.
     */
    public function getTagValues($key, $default=null)
    {
        $default= is_array($default)?$default:array($default); 
        $tags = $this->extractTags();
        $k=array_search($key, $tags['key']);
        return false === $k ? $default : explode(',', $tags['value'][$k]);
    }

    /**
     * Extracts all the subtags.
     *
     * @return array An associative array
     */
    public function extractTags($fresh=false)
    {
      static $matches = null;
      if($matches === null || $fresh) {
        $lines = $this->entity->getAnnotationValue($this->annotation);
        if(!is_array($lines)) $lines = array($lines);
        foreach($lines as $line) {
          preg_match_all('/(?P<key>[^= ]+)=(?P<value>[^= ]+)/i', $line, $matches);
        }
      }
      return $matches;
    }

}