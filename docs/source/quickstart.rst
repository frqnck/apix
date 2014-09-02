Quick Start
===========

The most basic example creates and serves a route that echos the content passed in through the
URL parameter called *name*.  This route would be accessed through http://www.example.com/hello/myname and
would return 'Hello myname'.

.. code-block:: php
    
    try {
        // Instantiate the server (using the default config)
        $api = new Apix\Server(require 'config.php');

        // Create a GET handler $name is required
        $api->onRead('/hello/:name', function($name) {
            return array('Hello, ' . $name);
        });

        $api->run();
        
    } catch (\Exception $e) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
    }


Another example using annotations.
    
.. code-block:: php

    try {
        // Instantiate the server (using the default config)
        $api = new Apix\Server(require 'config.php');
        
        // $type and $stuff are required parameters.
        // $optional is not mandatory.
        $api->onRead('/search/:type/with/:stuff/:optional',
            /**
             * Search for things by type that have stuff.
             *
             * @param     string  $type         A type of thing to search upon
             * @param     string  $stuff        One or many stuff to filter against
             * @param     string  $optional     An optional field
             * @return    array
             * @api_auth  groups=clients,employes,admins users=franck,jon
             * @api_cache ttl=12mins tags=searches,indexes
             */
            function($type, $stuff, $optional = null) {
                // some logic
                return $results;
            }
        );

        $api->run();
        
    } catch (\Exception $e) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
    }

config.php
----------
The following example configuration file is used in the above examples.  Details on the function
of these options may be found in the :doc:`/config` documentation.

.. code-block:: php

    <?php
    namespace Apix;

    $c = array(
        'api_version'       => '0.1.0.empty-dumpty',
        'api_realm'         => 'api.domain.tld',
        'output_rootNode'   => 'apix',
        'input_formats'     => array('post', 'json', 'xml'),
        'routing'           => array(
            'path_prefix'       => '/^(\/\w+\.\w+)?(\/api)?\/v(\d+)/i',
            'formats'           => array('json', 'xml', 'jsonp', 'html', 'php'),
            'default_format'    => 'json',
            'http_accept'       => true,
            'controller_ext'    => true,
            'format_override'   => isset($_REQUEST['_format'])
                                    ? $_REQUEST['_format']
                                    : false,
        )

    );

    // Resources definitions
    $c['resources'] = array(
        '/help/:path' => array(
            'redirect' => 'OPTIONS'
        ),
        '/*' => array(
            'redirect' => 'OPTIONS',
        )
    );

    // Service definitions
    $c['services'] = array(

        // Auth examples (see plugins definition)
        'auth_example' => function() use ($c) {
            $adapter = new Plugin\Auth\Basic($c['api_realm']);
            $adapter->setToken(function(array $current) use ($c) {
                $users = Service::get('users_example');
                foreach ($users as $user) {
                    if ($current['username'] == $user['username'] && $current['password'] == $user['api_key']) {
                        Service::get('session', $user);
                        return true;
                    }
                }

                return false;
            });
            return $adapter;
        },

        // This is used by the auth_example service defined above.
        'users_example' => function() {
            return array(
                0 => array(
                    'username' => 'myuser', 'password' => 'mypass', 'api_key' => '12345', 'group' => 'admin', 'realm' => 'www.example.com', 'ips' => '127.0.0.1'
                )
            );
        },

        // This is used by the auth_example service defined further above.
        'session' => function($user) {
            $session = new Session($user['username'], $user['group']);
            if (isset($user['ips'])) {
                $session->setTrustedIps((array) $user['ips']);
            }
            $session->addData('api_key', $user['api_key']);
            Service::set('session', $session);
        }

    );

    // Plugins definitions
    $c['plugins'] = array(
        'Apix\Plugin\OutputSign',
        'Apix\Plugin\OutputDebug' => array('enable' => DEBUG),
        'Apix\Plugin\Tidy',
        'Apix\Plugin\Auth' => array('adapter' => $c['services']['auth_example']),
    );

    // Init is an associative array of specific PHP directives. They are
    // recommended settings for most generic REST API server and should be set
    // as required. There is most probably a performance penalty setting most of
    // these at runtime so it is recommneded that most of these (if not all) be
    // set directly in PHP.ini/vhost file on productions servers -- and then
    // commented out. TODO: comparaison benchmark!?
    $c['init'] = array(
        'display_errors'            => DEBUG,
        'init_log_errors'           => true,
        'error_log'                 => '/tmp/apix-server-errors.log',
    );

    $c['default'] = array(
        'services' => array(),
        'resources' => array(
            'OPTIONS' => array(
                'controller' => array(
                    'name' => __NAMESPACE__ . '\Resource\Help',
                    'args' => null
                ),
            ),
            'HEAD' => array(
                'controller' => array(
                    'name' => __NAMESPACE__ . '\Resource\Test',
                    'args' => null
                ),
            ),
        )
    );
    $c['config_path'] = __DIR__;
    return $c;
