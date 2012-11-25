<?php

namespace Apix;

use Apix\Listener,
    Apix\Config,
    Apix\Resources,
    Apix\Entity,
    Apix\Request,
    Apix\HttpRequest,
    Apix\Response;

define('APIX_START_TIME', microtime(true));

class Server extends Listener
{
    const VERSION = '@package_version@';

    public $config = array(); // todo chnage this!
    public $entity = null;
    public $request = null;
    public $resources = null;
    public $response = null;

    public $route = null;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($config=null, Request $request=null, Response $response=null)
    {
        // Set and intialise the config
        $c = $config instanceOf Config ? $config : Config::getInstance($config);
        $this->config = $c->get();

        $this->initSet($this->config);

        // Set the current request
        $this->request = $request === null ? HttpRequest::getInstance() : $request;

        if ($this->request instanceOf HttpRequest) {
            $this->request->setFormats($this->config['input_formats']);
        }

        // Initialise the response
        $this->response =
            $response !== null
                ? $response
                : new Response(
                    $this->request,
                    $this->config['output_sign'],
                    $this->config['output_debug']
                );
        $this->response->setFormats($this->config['routing']['formats']);

        // Add all the resources from config.
        $this->resources = new Resources;
        foreach ($c->getResources() as $key => $values) {
            $this->resources->add(
                $key, $values
            );
        }
    }

   /**
     * Deals with PHP inits and error handlers.
     *
     * @param  array $configs The config entries to initialise.
     * @return void
     *
     * @codeCoverageIgnore
     */
    public function initSet(array $configs)
    {
        if(!defined('UNIT_TEST') && isset($configs['init'])
            ) {
            // set config inits
            foreach ($configs['init'] as $key => $value) {
                ini_set($key, $value);
            }
        }

        // set the generic errors & exception handlers
        set_error_handler(array('Apix\Exception', 'errorHandler'), E_ALL);
        register_shutdown_function(array('Apix\Exception', 'shutdownHandler'));
    }

    /**
    * Run the show...
    *
    * @throws \InvalidArgumentException 404
    *
    * @codeCoverageIgnore
    */
    public function run()
    {
        try {
            // set the routing
            $this->setRouting(
                $this->request,
                $this->resources->toArray(),
                $this->config['routing']
            );

            // attach the early listeners @ pre-processing stage
            $this->addAllListeners('server', 'early');

            $this->setResourceEntity($this->route);

        } catch (\Exception $e) {

            $http_code =  $e->getCode()>199 ? $e->getCode() : 500;
            $this->response->setHttpCode($http_code);

            $this->results['error'] = array(
                'message'   => $e->getMessage(),
                'code'      => $http_code
            );

            // set the error controller!
            if (
                !in_array(
                    $this->route->getController(),
                    array_keys($this->resources->toArray())
                )
            ) {
               $this->route->setController('error');
               $this->results = $this->results['error'];
            }

            // attach the listeners @ exception stage
            $this->addAllListeners('server', 'exception');
        }

        switch ($this->response->getHttpCode()) {
            case 401;
                // TODO
                #$this->response->setHeader('WWW-Authenticate',
                #    sprintf('%s realm="%s"', $this->config['auth']['type'], $this->config['org'])
                #);
            break;

            case 405:
                $this->response->setHeader('Allow',
                    implode(', ', array_keys(
                        $this->entity->getAllActions()
                    )),
                    false // preserve existing
                );

        }

        $output = $this->response->generate(
                $this->route,
                $this->results,
                $this->getServerVersion($this->config['api_realm'], $this->config['api_version']),
                $this->config['output_rootNode']
            );

        // attach the late listeners @ post-processing stage
        $this->addAllListeners('server', 'late');

        return $this->request->getMethod() != 'HEAD' ? $output : null;
    }

   /**
    * Retrieves (and calls) a resource entity from a given route.
    *
    * @param  Router $route
    * @return Entity
    */
    public function setResourceEntity(Router $route)
    {
        // get the entity object from a route
        $this->entity = $this->resources->get($route);

        // attach the early listeners @ pre-processing stage
        $this->entity->addAllListeners('entity', 'early');

        // set the results -- TODO: create a Response results obj to handles this
        $this->results = $this->entity->call($route);

        // attach the late listeners @ post-processing stage
        $this->entity->addAllListeners('entity', 'late');
    }

   /**
    * Gets the server version string.
    *
    * @return string
    *
    * @codeCoverageIgnore
    */
    private function getServerVersion($realm, $version)
    {
        return sprintf('%s/%s (%s)', $realm, $version, Server::VERSION);
    }

   /**
    * Sets and initialise the routing processes.
    *
    * @param  Request $request
    *
    * @return void
    */
    public function setRouting(Request $request, array $resources, array $opts=null)
    {
        $path = isset($opts['path_prefix'])
                ? preg_replace($opts['path_prefix'], '', $request->getUri())
                : $request->getUri();

        if ($path == '') {
            $path = '/';
        }

        // check controller_ext
        if ($opts['controller_ext']) {
            $parts = explode('/', $path);
            $info = pathinfo(isset($parts[1]) ? $parts[1] : $parts[0] );
            $ext = isset($info['extension'])?$info['extension']:null;
            if ($ext) {
                $path = preg_replace('/\.' . $ext . '/', '', $path, 1);
            }
            $rawController = $info['filename'];
        }

        $this->route = new Router(
            $resources,
            array(
                'method'    => $request->getMethod(),
                'path'      => $path,
                'server'    => & $this
            )
        );

        // Set the response format...
        if (null !== $opts) {
            $this->negotiateFormat($opts, isset($ext) ? $ext : false);
        }

        $this->route->map($path, $request->getParams());

        if (isset($rawController)) {
            $this->route->setController($rawController);
        }
    }

   /**
    * Returns the route object.
    *
    * @return Router
    */
    public function getRoute()
    {
        return $this->route;
    }

   /**
    * Returns the output format from the request chain.
    *
    * @param array $opts Options are:
    *                              - [default] => string e.g. 'json',
    *                              - [controller_ext] => boolean,
    *                              - [override] => false or string use $_REQUEST['format'],
    *                              - [http_accept] => boolean.
    * @param  string|false $ext The contoller defined extension.
    *
    * @return string
    */
    public function negotiateFormat(array $opts, $ext=false)
    {
        switch (true) {
            case $opts['controller_ext']
                && $format = $ext:
            break;

            case false !== $opts['format_override']
                && $format = $opts['format_override']:
            break;

            case $opts['http_accept']
                && $format = $this->request->getAcceptFormat():
            break;

            default:
                $format = $opts['default_format'];
        }

        $this->response->setFormat($format, $opts['default_format']);

        if ($opts['http_accept']) {
            $this->response->setHeader('Vary', 'Accept');
        }
    }








/* -- Closure prototyping  -- */

   /**
    * Proxy to resources::add (shortcut)
    *
    * @param  string     $method The HTTP method to match against.
    * @param  string     $path   The path name to match against.
    * @param  mixed      $to     Callback that returns the response when matched.
    * @return Controller
    * @see  Resources::add
    */
    protected function proxy($method, $path, \Closure $to)
    {
        return $this->resources->add($path,
            array(
                'action' => $to,
                'method' => $method
            )
        );
    }

   /**
    * Create / POST request handler
    *
    * @param string $path The path name to match against.
    * @param mixed  $to   Callback that returns the response when matched.
    * @see  Server::proxy
    * @return Controller Provides a fluent interface.
    */
    public function onCreate($path, $to)
    {
        return $this->proxy('POST', $path, $to);
    }

    /**
     * Read / GET request handler
     *
     * @param string $path The path name to match against.
     * @param mixed  $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onRead($path, $to)
    {
        return $this->proxy('GET', $path, $to);
    }

    /**
     * Update / PUT request handler
     *
     * @param string $path The path name to match against.
     * @param mixed  $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onUpdate($path, $to)
    {
        return $this->proxy('PUT', $path, $to);
    }

    /**
     * Modify / PATCH request handler
     *
     * @param string $path The path name to match against.
     * @param mixed  $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onModify($path, $to)
    {
        return $this->proxy('PATCH', $path, $to);
    }

    /**
     * Delete / DELETE request handler
     *
     * @param string $path The path name to match against.
     * @param mixed  $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onDelete($path, $to)
    {
        return $this->proxy('DELETE', $path, $to);
    }

    /**
     * Help / OPTIONS request handler
     *
     * @param string $path The path name to match against.
     * @param mixed  $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onHelp($path, $to)
    {
        return $this->proxy('OPTIONS', $path, $to);
    }

    /**
     * Test / HEAD request handler
     *
     * @param string $path The path name to match against.
     * @param mixed  $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onTest($path, $to)
    {
        return $this->proxy('HEAD', $path, $to);
    }

    /**
     * Test Read from a group.
     *
     * @param  array  $opts Options are:
     * @return string
     */
    public function setGroup($name)
    {
        $class = new \ReflectionClass($this);
        $method = $class->getMethod('setGroup');
    var_dump($class);

        $class = new \ReflectionFunction();
        //$method = $class->getMethod('setGroup');
    var_dump($class);

        $doc = $method->getDocComment();

        $this->group = array(
            'name'  => $name,
            'doc'   => $doc
        );
    }

    // *
    //  * Shortcut to HttpRequest::getBodyData
    //  *
    //  * @see  HttpRequest::getBodyData
    //  * @return array

    // public function getBodyData()
    // {
    //     return HttpRequest::getBodyData($this->request);
    // }

}
