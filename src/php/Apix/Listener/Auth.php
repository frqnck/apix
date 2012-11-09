<?php
namespace Apix\Listener;

class Auth extends AbstractListener
{

    public $annotation = 'api_auth';

    protected $options = array(
        'public_group'  =>  'public'     // name of the public group to skip.
    );

    /**
     * Constructor.
     *
     * @param Cache\Adapter $adapter
     * @param array $options Array of options.
     */
    public function __construct(Auth\Adapter $adapter, array $options=array())
    {
        $this->adapter = $adapter;
        $this->options = $options+$this->options;
    }

    public function update(\SplSubject $entity)
    {
        $this->entity = $entity;

        $groups = $this->getSubTagValues('groups');
        $users = $this->getSubTagValues('users');

        // skip if groups and users are null, or public role group.
        if( 
            null === $groups || in_array($this->options['public_group'], $groups)
            && null !== $users
        ) {
            return false;
        }

        // authenticate
        if( ! $username = $this->adapter->authenticate() ) {
            throw new \Exception('Authentication required', 401);
        }

        // check user
        if(null !== $users && !in_array($username, $users)) {
            $this->log('Access unauthorised', $username);
            throw new \Exception('Access unauthorised.', 401);
        }

        // todo set X_REMOTE_USER or X_AUTH_USER
        #$entity->getResponse()->setHeader('X_REMOTE_USER', $username);
        $_SERVER['X_AUTH_USER'] = $username;

        #$this->log('Access granted', $username);
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
