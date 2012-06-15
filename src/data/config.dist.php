<?php

$config = array(
    'api_realm'     => 'api.zenya.com',
    'api_version'   => '1.0',

    #return sprintf("%s/%s #%s", $app->config['realm'], $app->config['version'], Server::VERSION);
    #'test'=> $this['debug'],

    // output
    'output_rootNode'  => 'zenya',
    'output_sign'      => true,
    'output_debug'     => true
);

$config['services']['users'] = function($username, $realm) {
        // username:realm:sharedSecret
        $users = array(
            0=>array('username'=>'franck', 'realm'=>'sleepover.dev', 'sharedSecret'=>'pass', 'role'=>'admin'),
            1=>array('username'=>'bob', 'realm'=>'sleepover.dev', 'sharedSecret'=>'pass', 'role'=>'guest')
        );
        foreach($users as $id => $user)
        {
            if(
                #$user['username'] == $digest['username']
                #&& $user['realm'] == $this->realm
                $user['username'] == $username
                && $user['realm'] == $realm
            ) {
              return $user['sharedSecret'];
            }
        }

        return false;
};


return $config;
