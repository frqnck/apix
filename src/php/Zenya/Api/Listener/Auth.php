<?php
namespace Zenya\Api\Listener;

class Auth implements \SplObserver
{

    /**
     * Constructor.
     *
     * @param mixed $target Can be a file path (default: php://output), a resource,
     *                      or an instance of the PEAR Log class.
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     */
    public function __construct($target = 'php://output', array $events = array())
    {
        if (!empty($events)) {
            $this->events = $events;
        }
        if (is_resource($target) || $target instanceof Log) {
            // use Pear::Log
            $this->target = $target;
        } elseif (false === ($this->target = @fopen($target, 'ab'))) {
            throw new Exception("Unable to open '{$target}'", 500);
        }
    }

    public function update(\SplSubject $subject)
    {
        #echo '<pre>';
        #print_r($subject->server->resource->getMethods());

        #throw new \Exception("Auth error", 401);

        $action = $subject->server->route->getAction();
        $method = $subject->server->request->getMethod();

        // skip for user login, testing & help!
        switch (true):
            case $action === 'session' && $method === 'GET': // list
            case $action === 'session' && $method === 'POST': // login
            case $action === 'session' && $method === 'OPTIONS': // testing

            case $action === 'help':
                return;
        endswitch;

        // TODO HERE!!

        $headers = apache_request_headers();
        if (isset($headers['X-session_id'])) {
            $_POST['session_id'] = $headers['X-session_id']; // temp!
            Zend_Session::setId($headers['X-session_id']);
        }

        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity() === false) {
            Zend_Session::regenerateId();
            throw new Zenya_Api_Exception('Session_id invalid.', 401);
        }

        $user = Zenya_Service::getService('Default_Service_User');
        $user = $user->getUser();

        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        if ($user->session->ip != $ip) {
            Zend_Session::destroy();
            throw new Zenya_Api_Exception('Session\'s IP invalid.', 401);
        }

        /*
          // useless security wise but helpful for testing...
          $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
          if ($user->session->ua != $ua) {
          Zend_Session::destroy();
          throw new Zenya_Api_Exception('Session\'s UA invalid.', 401);
          }
         */
    }

}
