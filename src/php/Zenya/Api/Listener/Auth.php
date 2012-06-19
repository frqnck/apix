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
        //user => password
        #$this->users = array('admin' => 'mypass', 'guest' => 'guest');

        switch (true) {
            case ($auth instanceof Auth\Adapter):
              $this->adapter = $auth;
            break;

            case ($auth instanceof \Zend_Auth):
              echo "TODO: Zend_Auth";
              exit;

            default:
                throw new \RuntimeException("Unable to open the Authentication adapter");
        }

    }

    public function update(\SplSubject $resource)
    {
        // skip if public
        if($resource->isPublic()) {
          return;
        }
/*
        $action = $resource->route->getAction();

        $request = $resource->route->request;
        $method = $request->getMethod();
 */

        $username = $this->adapter->authenticate();
        if(!$username) {
            throw new Exception('Authentication has failed or has not yet been provided', 401);
        }
        
        $resource->response->setHeader('X_AUTH_USER', $username );

        #$_SERVER['X_AUTH_USER'] = $username;


        // temp
        // Authorization: Basic ZnJhbmNrOnRlc3Q=";
/*
        $username = 'franck';#$pass = 'pass';

        $conf = array(
            'accept_schemes'    => 'digest',
            'realm'             =>  'ZenyaApi',
            #'digest_domains'    => <string> Space-delimited list of URIs
            #'nonce_timeout'     => <int>
            #'use_opaque'        => <bool> Whether to send the opaque value in the header
            #'alogrithm'         => <string> See $_supportedAlgos. Default: MD5
            #'proxy_auth'        => <bool> Whether to do authentication as a Proxy
        );

        $adapter = new \Zend_Auth_Adapter_Http($conf);

        $result = $adapter->authenticate();
        $identity = $result->getIdentity();

        print_r($identity);exit;
*/
        return;
        $s = new \Zend_Session;

        $s = new \Zenya_Model_Session;

        if ($request->hasHeader('X-session_id')) {
            //$_POST['session_id'] = $headers['X-session_id']; // temp!
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