<?php

namespace Zenya\Api;

$c = array(
    'api_realm'     => 'api.zenya.com',
    'api_version'   => '1.0',

    // output
    'output_rootNode'  => 'zenya',
    'output_sign'      => true,
    'output_debug'     => true
);

// services
$c['services'] = array(
    'users' => function() {
        // TODO: retrive the users from somewhere?
        $users = array(
            // username:realm:sharedSecret:role
            0=>array('username'=>'franck', 'password'=>'123', 'realm'=>'api.zenya.com', 'sharedSecret'=>'apiKey', 'role'=>'admin'),
            1=>array('username'=>'bob', 'password'=>'123', 'realm'=>'api.zenya.com', 'sharedSecret'=>'pass', 'role'=>'guest')
        );
        return $users;
    }
);

// routes
$c['routes'] = array(
    #/:controller/paramName/:paramName/:id' => array(),
    #'/:controller/test' => array('class_name'=>'test'),

    '/help/:resource/:http_method/:filters' => array(
         'controller' => 'help',
    ),

    // '/category/:param1/:param2/:param3' => array(
    //     'controller' => 'Category',
    // ),

    '/auth/:param1' => array(
        'controller' => 'AuthResource'
    ),

    '/:controller/:param1/:param2' => array(
        #function() {echo '------ss';},
        #'controller' => 'BlankResource',
        #'class_name' => 'Zenya\Api\Fixtures\BlankResource',
        #'class_args' => array('classArg1' => 'test1', 'classArg2' => 'test2')
    )
);

// resources
$c['resources'] = array(
    // 'test' => array(
    //     'class_args'=>array('arg1'=>'value1', 'arg2'=>'string')
    // ),
    'resourceName' => array(
        'class_name' => 'Zenya\Api\Fixtures\BlankResource',
        'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
    ),
    'AuthResource' => array(
        'class_name' => 'Zenya\Api\Fixtures\AuthResource',
        'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
    ),

    'someName' => array(
        'class_name' => 'Zenya\Api\Fixtures\BlankResource',
        #'class_args' => array('test')
    )
);

// listeners
$c['listeners'] = array(
    'resource' => array(
        'early' => array(
            function() use ($c) {
                $adapter = new Listener\Auth\HttpDigest($c['api_realm']);
                $adapter->setToken = function(array $digest) use ($c, $adapter)
                { 
                    $users = $c['services']['users']();
                    foreach($users as $user)
                    {
                        if( // this should be altered accordingly!
                            $user['username'] == $digest['username']
                            && $user['realm'] == $c['api_realm']
                        ) {
                            #$_SERVER['X_AUTH_USER'] = $digest['username'];

                            // Could be set to password, apiKey or hashed mixture... 
                            $adapter->token = $user['sharedSecret'];
                            #return $user['sharedSecret'];
                        }
                    }

                    #return false;
                };

                return new Listener\Auth($adapter);
            },
        )
    )
);


/* Prototype:

function onRead($route, $call) {
// add route to config route
// add call to resources.
}

    $c->onRead('/auth/:param1', function($param1) {
        new Zenya\Api\Fixtures\AuthResource($param1);
        return array();
    });

    $c->onCreate('/auth/:param1', function() {
        new Zenya\Api\Fixtures\AuthResource();
        return array();
    });
*/

/*
$c['routes'] = array(
    // roue =>
    '/auth/:param1' => array(
        'class_name' => 'Zenya\Api\Fixtures\AuthResource',
        'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
    ),

);
*/
// 'AuthResource' => array(
//     'class_name' => 'Zenya\Api\Fixtures\AuthResource',
//     'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
// ),



return $c;