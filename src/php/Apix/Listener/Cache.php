<?php
namespace Apix\Listener;

class Cache implements \SplObserver
{
    protected $annotation = 'api_cache';
    protected $adapter;
    protected $defaults = array(
      'ttl'     => '10mins', // null stands forever!
      'tags'    => null,
      'flush'   => null
    );

    const REGEX = '/(?P<key>[^= ]+)=(?P<value>[^= ]+)/i';

    /**
     * Constructor.
     *
     * @param object $adapter
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     * @throws \RuntimeException
     */
    public function __construct($adapter=null)
    {
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

    function log($action, $ref)
    {
      if(defined('DEBUG')) {
        $str = sprintf('%s %s (%s)', __CLASS__, $action, $ref);
        error_log( $str );
      }
    }

    public function update(\SplSubject $entity)
    {
      // skip if null
      if (null === $entity->getAnnotationValue($this->annotation)) {
        return false;
      }

      $this->entity = $entity;

      // clean tags explicitly
      if ( $tags = $this->getTag('clean') ) {
        $this->log('cleaning tags', implode(', ', $tags));
        $this->adapter->clean($tags);
      }

      // the cache id is simply the entity route name for now!
      $id = $entity->getRoute()->getName();

      // Use cache
      if ($output = $this->adapter->load($id)) {
        $this->log('loading', $id);

        return $output;
      }

      // retrieve the output...
      $output = call_user_func_array(
        array($entity, 'call'),
        array($entity->getRoute())
      );

      // ...and cache it.
      $ttl = $this->getTtl();
      $this->log(sprintf('saving for %d seconds.', $ttl), $id);
      $this->adapter->save($output, $id, $this->getTag('tags'), $ttl);

      return $output;
    }

    /**
     * Returns the TTL in seconds.
     * @return integer  The TTL in seconds.
     */
    public function getTtl()
    {
      $ttl = $this->getTag('ttl', $this->defaults['ttl']);
      return strtotime($ttl[0])-strtotime('NOW');
      // $expiryDate = new \DateTime();
      // $interval = new \DateInterval($ttl);
      // return $expiryDate->add($interval);
    }

    /**
     * Retuns the specifified tag.
     *
     * @param  [type] $key     [description]
     * @param  [type] $default [description]
     * @return [type]          [description]
     */
    public function getTag($key, $default=null)
    {
      $tags = $this->extractTags();
      $k=array_search($key, $tags['key']);
      if(false === $k) {
        return $default;
      }
      return explode(',', $tags['value'][$k]);
    }

    /**
     * Extracts all the tags.
     *
     * @return array An associative array
     */
    public function extractTags()
    {
      static $matches = null;

      if($matches === null) {
        $lines = $this->entity->getAnnotationValue($this->annotation);

        if(!is_array($lines)) $lines = array($lines);
        foreach($lines as $line) {
          preg_match_all(self::REGEX, $line, $matches);
        }
      }
      return $matches;
    }

}