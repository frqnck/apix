<?php
namespace Apix\Listener;

class Auth implements \SplObserver
{

    public $annotation = 'api_auth_role';

    /**
     * Constructor.
     *
     * @param object $adapter The cache adapter.
     *
     * @throws \RuntimeException
     */
    public function __construct($adapter=null)
    {

        switch (true) {
            case ($adapter instanceof Auth\Adapter):
              $this->adapter = $adapter;
            break;

            case ($adapter instanceof \Zend_Auth):
              echo "TODO: Zend_Auth";
              exit;

            default:
                throw new \RuntimeException('Unable to open the Authentication adapter');
        }

    }

    public function update(\SplSubject $entity)
    {
        echo '(a) ';

        $role = $entity->getAnnotationValue($this->annotation);

        // skip if null or public
        if (is_null($role) || $role == 'public') {
          return false;
        }

        if( ! $username = $this->adapter->authenticate() ) {
            throw new \Exception('Authentication required', 401);
        }

        // todo set X_REMOTE_USER or X_AUTH_USER
        #$entity->getResponse()->setHeader('X_REMOTE_USER', $username);
        $_SERVER['X_AUTH_USER'] = $username;

        return $username;






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
