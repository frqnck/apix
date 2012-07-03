<?php
namespace Zenya\Api\Listener;

class Auth implements \SplObserver
{

    /**
     * Constructor.
     *
     * @param object $adapter Can be a file path (default: php://output), a resource,
     *                      or an instance of the PEAR Log class.
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     * @throws \RuntimeException
     */
    public function __construct($auth=null)
    {

        switch (true) {
            case ($auth instanceof Auth\Adapter):
              $this->adapter = $auth;
            break;

            case ($auth instanceof \Zend_Auth):
              echo "TODO: Zend_Auth";
              exit;

            default:
                throw new \RuntimeException('Unable to open the Authentication adapter');
        }

    }

    public function update(\SplSubject $entity)
    {
        // skip if public
        if($entity->isPublic()) {
          return;
        }

        $username = $this->adapter->authenticate();
        if(!$username) {
            throw new Exception('Authentication Required', 401);
        }

        // todo set X_REMOTE_USER or X_AUTH_USER
        #$entity->response->setHeader('X_REMOTE_USER', $username);
        $_SERVER['X_AUTH_USER'] = $username;

        return;

        $s = new \Zenya_Model_Session;

        if ($request->hasHeader('X-session_id')) {
            \Zend_Session::setId( $request->getHeader('X-session_id') );
        }

        $auth = \Zend_Auth::getInstance();
        if ($auth->hasIdentity() === false) {
            \Zend_Session::regenerateId();
            throw new Exception('Session_id invalid.', 401);
        }

        $user = Zenya_Service::getService('Default_Service_User');
        $user = $user->getUser();

        if ($user->session->ip != $request->getIp()) {
            \Zend_Session::destroy();
            throw new Exception('Session\'s IP invalid.', 401);
        }

        // check UA string
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        if ($user->session->ua != $ua) {
        Zend_Session::destroy();
        throw new Zenya_Api_Exception('Session\'s UA invalid.', 401);
        }
    }

}