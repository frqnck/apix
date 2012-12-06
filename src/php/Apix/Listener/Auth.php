<?php
namespace Apix\Listener;

class Auth extends AbstractListenerEntity
{
    public static $hook = array('entity', 'early');

    protected $options = array(
        'adapter'       => 'Apix\Listener\Auth\Adapter',
        'enable'        => true,        // wether to enable or not
        'public_group'  => 'public',    // public group to skip auth
    );

    protected $annotation = 'api_auth';

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
