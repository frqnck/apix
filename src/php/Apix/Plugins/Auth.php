<?php
namespace Apix\Plugins;

class Auth extends PluginAbstractEntity
{
    public static $hook = array('entity', 'early');

    protected $options = array(
        'adapter'       => 'Apix\Plugins\Auth\Adapter',
        'enable'        => true,        // wether to enable or not
        'public_group'  => 'public',    // public group to skip auth
    );

    protected $annotation = 'api_auth';

    public function update(\SplSubject $entity)
    {
        $this->entity = $entity;

        $groups = $this->getSubTagValues('groups');
        $users = $this->getSubTagValues('users');

        // skip if groups and users are null, or if the group is public.
        if(
            null === $groups
            || in_array($this->options['public_group'], $groups)
            && null !== $users
        ) {
            return false;
        }

        // authenticate
        $username = $this->adapter->authenticate();

        if (!$username) {
            $this->log('Login failed', $username, 'INFO');
            $this->adapter->send();
            throw new \Exception('Authentication required', 401);
            exit;
        }

        // Check the user is authorised
        if (null !== $users && !in_array($username, $users)) {
            $this->log('Login unauthorised', $username, 'INFO');
            throw new \Exception('Access unauthorised.', 401);
        }

        $this->log('Login', $username, 'NOTICE');

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
