Example Project
===============

Project Layout
--------------

Assume that our project is laid out as follows (using Composer):

.. code-block:: text

    MyProject/
    ├── composer.json
    ├── config/
    │   ├── config.php
    │   ├── credentials.php
    │   ├── plugins.php
    │   ├── resources.php
    │   └── services.php
    ├── controllers/
    │   ├── Goodbye.php
    │   └── Hello.php
    ├── models/
    ├── public/
    │   ├── .htaccess
    │   └── index.php
    └── vendor/
        ├── apix/
        │   ├── apix/
        │   └── cache/
        ├── autoload.php
        └── composer/

For the sake of this example, we'll put ``MyProject`` directly in our webroot.  In most environments, you will want to expose **only** the ``public`` directory.  Download :download:`MyProject here <MyProject.zip>`.

composer.json
-------------

We use Composer to pull in the required external libraries, including the APIx framework and the APIx\Cache library.  A ``composer.json`` file for our project might look something like this (assuming you layout your controllers and models using the PSR-4 specification:

.. code-block:: javascript
    
    {
	"name":"myproject/myproject",
	"require": {
	    "apix/apix": "0.3.*",
	    "apix/cache": "1.1.*"
	},
	"autoload": {
	    "psr-4":{
		"MyProject\\Controllers\\": "controllers/",
		"MyProject\\Models\\": "models/"
	    }
	}
    }


Configuration
-------------

Lets first look at what a sample ``./config/config.php`` file might look like.  Bear in mind that this is an example, and none of these extra configuration files are actually necessary.  You could easily edit everything in a single file.  Then we'll look at each of the required configuration files that help us define our RESTful API.

.. code-block:: php

    <?php
    
    define('DEBUG', true);
    
    // Set our configuration variable to the default value
    $config = require "../vendor/apix/apix/src/data/distribution/config.dist.php";
    $config['api_version']     = '0.0.1.spamandeggs';
    $config['api_realm']       = 'api.myproject.com';
    $config['output_rootNode'] = 'myproject';
    
    // We're testing this using Apache with no virtual hosts - so we'll have to redefine
    // the routing path_prefix
    $config['routing']['path_prefix'] = '/^\/MyProject\/public\/v(\d+)/i';
    
    // Include credentials that we can use elsewhere in custom defined services, etc.
    $config['credentials']     = require 'credentials.php';
    
    // Include the resources we have defined in our resources.php configuration file
    $config['resources']      += require 'resources.php';
    
    // Include the services we have defined in our services.php configuration file.
    // If a service is redefined in the services.php file, use that instead.
    $config['services']       = array_merge($config['services'], require 'services.php');
    
    // Include the plugins we have defined in our plugins.php configuration file
    $config['plugins']        = array_merge($config['plugins'], require 'plugins.php');
    
    return $config;

config/credentials.php
^^^^^^^^^^^^^^^^^^^^^^^

The credentials file is used to store any credentials used to make connections to an outside data source.  For example, you might store information about your caching server or database connections.

.. code-block:: php
    
    <?php
    
    return array(
	// use a Redis instance for caching
	'redis' => array(
	    'servers' => array(
		array('127.0.0.1', 6379)
	    ),
	    'options' => array(
		'atomicity' => false,
		'serializer' => 'php'
	    )
	)
    );
    

config/resources.php
^^^^^^^^^^^^^^^^^^^^^

The resources file is where we'll store information about all of our available routes.  We'll be using class based controllers in this example.  If we wanted to use closures, we could define these as lambda functions.

.. code-block:: php
    
    <?php
    
    return array(
	'hello/:name' => array(
	    'controller' => array(
		'name' => 'MyProject\Controllers\Hello',
		'args' => null
	    )
	),
	'goodbye/:name' => array(
	    'controller' => array(
		'name' => 'MyProject\Controllers\Goodbye',
		'args' => null
	    )
	)
    );

We've now defined two routes that we'll be able to access at http://api.example.com/v1/hello/:name and http://api.example.com/v1/goodbye/:name.  The HTTP Method (:rfc:`2616`) available for these functions will be defined directly in the controllers themselves.

config/services.php
^^^^^^^^^^^^^^^^^^^^

We define a caching adapter which can be used through the project as a whole, and also by the caching plugin to allow for easy caching of output content.  If you include this service while trying out this example, you **will** have to set up a Redis instance.  If you'd prefer to skip this, simply return an empty array both here and in the plugins configuration file.

.. code-block:: php
    
    <?php
    
    use Apix\Cache;
    
    return array(
	// we'll reference the existing $config variable to retrieve our redis credentials
	'cache' => function() use ($config) {
	    $redis = new \Redis();
	    foreach($config['credentials']['redis']['servers'] as $redis_server) {
		$redis->connect($redis_server[0], $redis_server[1]);
	    }
	    $adapter = new Cache\Redis($redis, $config['credentials']['redis']['options']);
	    
	    // Reset this service definition so that continuous calls do not recreate a new adapter
	    // but simply return the existing one.
	    Service::set('cache', $adapter);
	    return $adapter;
	}
    );

config/plugins.php
^^^^^^^^^^^^^^^^^^^

We can define our own plugins if we choose.  Lets add in caching capabilities, which are not turned on in the default conguration.  We'll be relying on the `Apix\Cache <https://github.com/frqnck/apix-cache>`_ library to provide the caching adapter.  The caching adpater will be defined in the services configuration file.  This example also assumes that the services configuration file has already been processed, as it makes use of the cache service defined there.

.. code-block:: php
    
    <?php
    
    return array(
	// Plugin to cache the output of the controllers. The full Request-URI acts as
	// the unique cache id.  Caching is enabled through a controller method or closure's
	// annotation
	// e.g. * @api_cache  ttl=5mins  tags=tag1,tag2  flush=tag3,tag4
	'Apix\Plugin\Cache' => array('enable'=>false, 'adapter'=>$config['services']['cache'])
    );

Controllers
-----------

We've defined two resources above that each point to separate controller classes.

controllers/Goodbye.php
^^^^^^^^^^^^^^^^^^^^^^^^

The following controller will define a ``GET`` resource.

.. code-block:: php
    
    <?php
    
    namespace MyProject\Controllers;
    use Apix\Request;
    use Apix\Response;
    
    /**
     * Goodbye
     *
     * Lets say goodbye to people nicely.
     *
     * @api_public  true
     * @api_version 1.0
     * @api_auth    groups=public
     */
    class Goodbye {
	
	/**
	 * Goodbye
	 *
	 * Say Goodbye
	 *
	 * @param      string     $name        Who should we say goodbye to?
	 * @return     array
	 * @api_cache  ttl=60sec  tag=goodbye  Cache this call for 60 seconds
	 */
	public function onRead(Request $request, $name) {
	    if(strlen(trim($name)) == 0) {
		throw new \Exception("I don't know who I'm saying goodbye to!");
	    }
	    
	    return array("goodbye" => "goodbye, " . trim($name));
	}
    }

controllers/Hello.php
^^^^^^^^^^^^^^^^^^^^^^

The following controller will define both ``GET`` and ``POST`` resources.  Other methods could also be defined here using the typical **CRUD** methods.

.. code-block:: php
    
    <?php
    
    namespace MyProject\Controllers;
    use Apix\Request;
    use Apix\Response;
    
    /**
     * Hello
     *
     * Lets say hello to people nicely.
     *
     * @api_public  true
     * @api_version 1.0
     * @api_auth    groups=public
     */
    class Hello {
	
	/**
	 * Hello
	 *
	 * Say Hello to someone
	 *
	 * @param      string     $name        Who should we say hello to?
	 * @return     array
	 * @api_cache  ttl=60sec  tag=goodbye  Cache this call for 60 seconds
	 */
	public function onRead(Request $request, $name) {
	    if(strlen(trim($name)) == 0) {
		// Return a 400 if they didn't pass in a name
		throw new \Exception("I don't know who I'm saying hello to!", 400);
	    }
	    
	    return array("greeting" => "hello, " . trim($name));
	}
	
	/**
	 * Hello
	 *
	 * Say hello to someone using the POSTED greeting.
	 *
	 * @param      string     $name        Who should we say hello to?
	 * @param      string     $greeting    How should we say hello?
	 * @return     array
	 * @api_cache  ttl=60sec  tag=goodbye  Cache this call for 60 seconds
	 */
	public function onCreate(Request $request, $name) {
	    if(strlen(trim($name)) == 0) {
		// Return a 400 if they didn't pass in a name
		throw new \Exception("I don't know who I'm saying hello to!", 400);
	    }
	    
	    $data = $request->getBodyData();
	    if($data == null || !is_array($data)) {
		// Return a 400 if they didn't pass in any POST data
		throw new \Exception("Could not read the POST request body", 400);
	    }
	    $greeting = array_key_exists('greeting', $data) ? (string) $data['greeting'] : "hello";
	    
	    return array("greeting" => $greeting . ', ' . trim($name));
	}
    }

public/index.php
----------------

In this example, all calls to our API will be directed through the main index file.  By exposing only the ``public`` directory via our webserver, we can effectively protect the other content in our project tree.  This helps to avoid security leaks caused by the accidental presence of a temporary swap file or leftover text file that might leak confidential information.

.. code-block:: php
    
    <?php
    
    require_once '../vendor/autoload.php';
    
    try {
	
	$api = new Apix\Server(require '../config/config.php');
	echo $api->run();
    } catch (\Exception $e) {
	header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
	die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
    }

public/.htaccess
----------------

.. code-block:: text
    
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} -s [OR]
    RewriteCond %{REQUEST_FILENAME} -l [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^.*$ - [NC,L]
    RewriteRule ^.*$ index.php [NC,L]

Try it out
----------

When all is appropriately setup, access the following URL to access self-generated documentation:  http://localhost/MyProject/public/v1/help?_format=json.  You should see something like the following:

.. code-block:: javascript

    {
	"myproject": {
	    "help": {
		"items": [
		    {
			"title": "Hello",
			"description": "Lets say hello to people nicely.",
			"api_public": "true",
			"api_version": "1.0",
			"api_auth": "groups=public",
			"methods": {
			    "GET": {
				"title": "Hello",
				"description": "Say Hello to someone",
				"params": {
				    "name": {
					"type": "string",
					"name": "name",
					"description": "Who should we say hello to?",
					"required": true
				    }
				},
				"return": "array",
				"api_cache": "ttl=60sec  tag=goodbye  Cache this call for 60 seconds"
			    },
			    "POST": {
				"title": "Hello",
				"description": "Say hello to someone using the POSTED greeting.",
				"params": {
				    "name": {
					"type": "string",
					"name": "name",
					"description": "Who should we say hello to?",
					"required": true
				    },
				    "greeting": {
					"type": "string",
					"name": "greeting",
					"description": "How should we say hello?",
					"required": false
				    }
				},
				"return": "array",
				"api_cache": "ttl=60sec  tag=goodbye  Cache this call for 60 seconds"
			    }
			},
			"path": "\/hello\/:name"
		    },
		    {
			"title": "Goodbye",
			"description": "Lets say goodbye to people nicely.",
			"api_public": "true",
			"api_version": "1.0",
			"api_auth": "groups=public",
			"methods": {
			    "GET": {
				"title": "Goodbye",
				"description": "Say Goodbye",
				"params": {
				    "name": {
					"type": "string",
					"name": "name",
					"description": "Who should we say goodbye to?",
					"required": true
				    }
				},
				"return": "array",
				"api_cache": "ttl=60sec  tag=goodbye  Cache this call for 60 seconds"
			    }
			},
			"path": "\/goodbye\/:name"
		    },
		    {
			"title": "Help",
			"description": "This resource entity provides in-line referencial to all the API resources and methods.",
			"methods": {
			    "GET": {
				"title": "Display the manual of a resource entity",
				"description": "This resource entity provides in-line referencial to all the API resources and methods.\nBy specify a resource and method you can narrow down to specific section.\ncommunication options available on the request\/response chain\nidentified by the Request-URI. This method allows the client to determine\nthe options and\/or requirements associated with a resource,\nor the capabilities of a server, without implying a resource action or\ninitiating a resource retrieval.",
				"params": {
				    "path": {
					"type": "string",
					"name": "path",
					"description": "A string of characters used to identify a resource.",
					"required": false
				    },
				    "filters": {
					"type": "array",
					"name": "filters",
					"description": "Filters can be use to narrow down the resultset.",
					"required": false
				    }
				},
				"example": "\u003Cpre\u003EGET \/help\/path\/to\/entity\u003C\/pre\u003E",
				"id": "help",
				"usage": "The OPTIONS method represents a request for information about the\ncommunication options available on the request\/response chain\nidentified by the Request-URI. This method allows the client to determine\nthe options and\/or requirements associated with a resource,\nor the capabilities of a server, without implying a resource action or\ninitiating a resource retrieval.",
				"see": "\u003Cpre\u003Ehttp:\/\/www.w3.org\/Protocols\/rfc2616\/rfc2616-sec9.html#sec9.2\u003C\/pre\u003E"
			    },
			    "OPTIONS": {
				"title": "Outputs info for a resource entity.",
				"description": "The OPTIONS method represents a request for information about the\ncommunication options available on the request\/response chain\nidentified by the Request-URI. This method allows the client to determine\nthe options and\/or requirements associated with a resource,\nor the capabilities of a server, without implying a resource action or\ninitiating a resource retrieval.",
				"params": {
				    "server": {
					"type": "Server",
					"name": "server",
					"description": "The main server object.",
					"required": true
				    },
				    "filters": {
					"type": "array",
					"name": "filters",
					"description": "An array of filters.",
					"required": false
				    }
				},
				"return": "array  The array documentation.",
				"api_link": [
				    "OPTIONS \/path\/to\/entity",
				    "OPTIONS \/"
				],
				"private": "1"
			    }
			},
			"path": "OPTIONS"
		    },
		    {
			"title": null,
			"description": "",
			"methods": {
			    "HEAD": {
				"title": "HTTP HEAD: test action handler",
				"description": "The HEAD method is identical to GET except that the server MUST NOT return\na message-body in the response. The metainformation contained in the HTTP\nheaders in response to a HEAD request SHOULD be identical to the information\nsent in response to a GET request. This method can be used for obtaining\nmetainformation about the entity implied by the request without transferring\nthe entity-body itself. This method is often used for testing hypertext links\nfor validity, accessibility, and recent modification.",
				"link": "http:\/\/www.w3.org\/Protocols\/rfc2616\/rfc2616-sec9.html#sec9.4",
				"return": "null",
				"cacheable": "true",
				"codeCoverageIgnore": ""
			    }
			},
			"path": "HEAD"
		    }
		]
	    },
	    "signature": {
		"resource": "GET ",
		"status": "200 OK - successful",
		"client_ip": "127.0.0.1"
	    },
	    "debug": {
		"timestamp": "Thu, 13 Mar 2014 21:15:48 GMT",
		"request": "GET \/MyProject\/public\/v1\/help?_format=json HTTP\/1.1",
		"headers": {
		    "Vary": "Accept"
		},
		"output_format": "json",
		"router_params": [
		    "help"
		],
		"memory": "1.21 MB~1.23 MB",
		"timing": "0.014 seconds"
	    }
	}
    }

Test out POSTing to the ``/hello/:name`` resource using curl.

``curl -X POST -d "greeting=hola" http://localhost/MyProject/public/v1/hello/world?_format=json``

.. code-block:: json
    
    {
	"myproject": {
	    "hello": {
		"greeting": "hola, world"
	    },
	    "signature": {
		"resource": "POST \/hello\/:name",
		"status": "200 OK - successful",
		"client_ip": "127.0.0.1"
	    },
	    "debug": {
		"timestamp": "Thu, 13 Mar 2014 21:20:31 GMT",
		"request": "POST \/MyProject\/public\/v1\/hello\/world?_format=json HTTP\/1.1",
		"headers": {
		    "Vary": "Accept"
		},
		"output_format": "json",
		"router_params": {
		    "name": "world"
		},
		"memory": "1.14 MB~1.15 MB",
		"timing": "0.014 seconds"
	    }
	}
    }


















