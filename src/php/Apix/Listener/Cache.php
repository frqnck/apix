<?php
namespace Apix\Listener;

class Cache implements \SplObserver
{

    private $_obj;
    private $_backend;

    private static $_defaultOptions = array(
      'tags' => '', 
      'ttl' => 'PT5M'
    );

    public $annotation = 'api_cache';

    /**
     * Constructor.
     *
     * @param object $adapter Can be a file path (default: php://output), a resource,
     *                        or an instance of the PEAR Log class.
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     * @throws \RuntimeException
     */
    public function __construct($adapter=null)
    {
      /*
          switch (true) {
              case ($adapter instanceof Cache\Adapter):
                $this->adapter = $adapter;
              break;

              default:
                  throw new \RuntimeException('Unable to open the Cache adapter');
          }
      */
    }

    public function update(\SplSubject $entity)
    {

        echo '(c) ';
        $res = call_user_func_array(array($entity, '_call'), array($entity->getRoute()));

        //$entity->underlineCall() = 'dd';

        // echo '<pre>';
        //   print_r(
        //     $entity
        //   );
        #exit;



        $role = $entity->getAnnotationValue($this->annotation);

        // skip if null or public
        if (is_null($role) || $role == 'public') {
          return false;
        }

        if( ! $username = $this->adapter->authenticate() ) {
            throw new \Exception('Authentication required', 401);
        }

        // todo set X_REMOTE_USER or X_AUTH_USER
        # $entity->getResponse()->setHeader('X_REMOTE_USER', $username);
        $_SERVER['X_AUTH_USER'] = $username;

        return $username;
    }

}