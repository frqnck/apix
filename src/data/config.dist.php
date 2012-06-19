<?php

namespace Zenya\Api;
#$app = Config::getInstance();


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
        // TODO: retrieve the users from somewhere, caching strategy?
        $users = array(
            // username:realm:sharedSecret:role
            0=>array('username'=>'franck', 'password'=>'123', 'realm'=>'api.zenya.com', 'sharedSecret'=>'apiKey', 'role'=>'admin'),
            1=>array('username'=>'bob', 'password'=>'123', 'realm'=>'api.zenya.com', 'sharedSecret'=>'sesame', 'role'=>'guest')
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

    '/upload/:type/:debug' => array(
        'controller' => 'UploadResource',
        'type'  => null,
        'debug'  => null,
    ),


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
    'UploadResource' => array(
        'class_name' => 'Zenya\Api\Fixtures\UploadResource',
        #'class_args' => array()
    ),
    'someName' => array(
        'class_name' => 'Zenya\Api\Fixtures\BlankResource',
        #'class_args' => array('test')
    )
);

// listeners
$c['listeners'] = array(
    'resource' => array(

        // fires early @ resource discovery stage
        'early_off' => array(
            // Basic Auth
            function() use ($c)
            {
                $adapter = new Listener\Auth\Basic($c['api_realm']);
                $adapter->setToken = function(array $basic) use ($c, $adapter)
                {
                    $users = Services::get('users');
                    foreach($users as $user)
                    {
                        if(
                            $user['username'] == $basic['username']
                            && $user['sharedSecret'] == $basic['password']
                        ) {
                            return $adapter->token = true;
                        }
                    }
                    $adapter->token = false;
                };

                return new Listener\Auth($adapter);
            },

        ),

       // fires early @ resource discovery stage
        'early' => array(
            // Digest Auth
            function() use ($c)
            {
                $adapter = new Listener\Auth\Digest($c['api_realm']);
                $adapter->setToken = function(array $digest) use ($c, $adapter)
                {
                    print_r($digest);
                    $users = Services::get('users');
                    foreach($users as $user)
                    {
                        if( // this should be altered accordingly!
                            $user['username'] == $digest['username']
                            && $user['realm'] == $c['api_realm']
                        ) {
                            // Can be set to password, apiKey, or hashed mixture...
                            return $adapter->token = $user['sharedSecret'];
                        }
                    }
                    $adapter->token = false;
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