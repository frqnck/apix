Plugins
=======

APIx comes prepackaged with several plugins, but there is nothing stopping you from
extending this functionality and building your own.  There are two available builtin
plugin types that you may extend which implement an Observer patter using `SplObserver <http://www.php.net/manual/en/class.splobserver.php>`_.  The basic plugin architecture
is relatively straightforward but we'll delve specifically into the :ref:`EntityPlugins` below.

Plugins are activated by "hook" calls throughout the APIx process and should implement the
`SplObserver::update <http://www.php.net/manual/en/splobserver.update.php>`_ method.  The
plugin hook is defined by a static variable of the same name in your Plugin class.

Here is an example taken from the :php:class:`OutputDebug` class.

.. code-block:: php
    
    <?php
    
    namespace Apix\Plugin;
    use Apix\Response;

    class OutputDebug extends PluginAbstract {

	public static $hook = array('response', 'early');
	
	protected $options = array(
	    'enable'     => false,              // whether to enable or not
	    'name'       => 'debug',            // the header name
	    'timestamp'  => 'D, d M Y H:i:s T', // stamp format, default to RFC1123
	    'extras'     => null,               // extras to inject, string or array
	);
	
	public function update(\SplSubject $response) {
	    if (false === $this->options['enable']) {
		return false;
	    }

	    $request = $response->getRequest();
	    $route = $response->getRoute();
	    $headers = $response->getHeaders();
	    $data = array(
		'timestamp'     => gmdate($this->options['timestamp']),
		'request'       => sprintf('%s %s%s',
					$request->getMethod(),
					$request->getRequestUri(),
					isset($_SERVER['SERVER_PROTOCOL'])
					? ' ' . $_SERVER['SERVER_PROTOCOL'] : null
				  ),
		'headers'       => $headers,
		'output_format' => $response->getFormat(),
		'router_params' => $route->getParams(),
	    );

	    if (defined('APIX_START_TIME')) {
		$data['timing'] = round(microtime(true) - APIX_START_TIME, 3) . ' seconds';
	    }

	    if (null !== $this->options['extras']) {
		$data['extras'] = $this->options['extras'];
	    }

	    $name = $this->options['name'];
	    $response->results[$name] = $data;
	}
    }

As you can see, this plugin will be activated at the beginning (``early``) of the ``response``
event and makes some edits to the response object.

Hooks
-----

There are several events that a plugin may hook into.  In order of when they fire, they are:

* server, early
* entity, early
* entity, late
* server, exception // if there is an exception
* response, early
* response, late
* server, late

Server Hook
^^^^^^^^^^^

The Server hook allows a user to create plugins that run before and after an entity is run and the response objects are generated.  The main server (:php:class:`Apix\Server`) is passed in to the
plugin's update function.

Entity Hook
^^^^^^^^^^^

The Entity Hooks fire before and after the required resource is called.  For example, if you're using
the class method for controllers and are calling :php:func:`onRead()`, the entity hooks will fire
immediately preceding and after that call.  Plugins that use the entity hooks will receive the entity
object as the parameter in their update function.

An example that uses the "entity, early" hook is the Authentication plugin which checks to see whether
the requested resource is protected and then serves based on satisfying a permissions check.

Response Hook
^^^^^^^^^^^^^

The Response hook is used to manipulate a response object following the completion of the entity calls.
The "early" hook allows access to the response object before it is encoded into the requested format.
The "late" hook allows access to the response object *after* is has been encoded.  Plugins that use the 
response hooks will receive the response object as the parameter in their update function.

Some examples of plugins that use the "response, early" hook include the OutputDebug and OutputSign
plugins.  The Tidy plugin makes use of the "response, late" hook in order to clean up the response
output after it has been encoded appropriately (into JSON, XML, HTML, etc).

.. _entityplugins:

Entity Plugins
--------------

Entity Plugins have the unique ability to access the method or closure annotations of the entities that they associate with.  The annotations are parsed and then available for use in the ``update`` method.
The annotation tag is defined using the :php:attr:`PluginAbtractEntity::$annotation` property of your
plugin.  APIx will then look in your entity definitions for the specified tag and parse out key=value
pairs.

In the following example we'll write a very quick (and incomplete) plugin that logs usage if the entity
is successfully called.  The adapter should implement :php:class:`My\\Usage\\LogAdapter` which in
this example would have a :php:func:`log` method which would, presumably, log usage.  This plugin will
use the @api_logusage annotation.  If the annotation doesn't exist in the entity, this plugin will not
call the adapter's log method.

.. code-block:: php
    
    <?php
    
    namespace Apix\Plugin;

    class UsageLogPlugin extends PluginAbstractEntity {
	
	public static $hook = array('entity', 'late');
	protected $options = array('adapter' => 'My\Usage\LogAdapter');
	protected $annotation = 'api_logusage'
	
	public function update(\SplSubject $entity) {
	    $method = $this->getSubTagValues('method');
	    $value = $this->getSubTagValues('value');
	    
	    if($method != null) {
		$this->adapter->log($method, $value);
	    }
	}
	
    }
	
An example entity that makes use of the above plugin might look like this:

.. code-block:: php
    
    <?php
    
    use Apix\Request;
    
    class Echo {
	
	/**
	 * Echo out the data that was POSTed
	 * 
	 * @return array
	 * @api_logusage method=echo value=1
	 */
	public function onCreate(Request $request) {
	    $data = $request->getBodyData();
	    return array("echo" => $data);
	}
    
    }












