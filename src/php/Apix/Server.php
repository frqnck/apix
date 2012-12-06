<?php

namespace Apix;

use Apix\Listener,
    Apix\Config,
    Apix\Resources,
    Apix\Entity,
    Apix\Request,
    Apix\HttpRequest,
    Apix\Response;

class Server extends Listener
{
    const VERSION = '@package_version@';

    /**
     * @todo review this.
     * @var array
     */
    public $config = array();

    /**
     * @var Request
     */
    public $request = null;

    /**
     * @var Route
     */
    public $route = null;

    /**
     * @var Resources
     */
    public $resources = null;

    /**
     * @var Entity
     */
    public $entity = null;

    /**
     * @var Response
     */
    public $response = null;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct(
        $config=null, Request $request=null, Response $response=null
    ) {

        // Set and intialise the config
        $c = $config instanceOf Config ? $config : Config::getInstance($config);
        $this->config = $c->get();

        $this->initSet($this->config);

        // Load all the plugins
        $this->loadPlugins($c->get('plugins'));

        // Set the current request
        $this->request =    null === $request
                            ? HttpRequest::getInstance()
                            : $request;

        if ($this->request instanceOf HttpRequest) {
            $this->request->setFormats($this->config['input_formats']);
        }

        // Initialise the response
        $this->response =   null === $response
                            ? new Response($this->request)
                            : $response;

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
     * @codeCoverageIgnore
     */
    private function initSet(array $configs)
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
            $this->response->setRoute($this->route);

            // early listeners @ pre-server
            $this->hook('server', 'early');

            // get the entity object from a route
            $this->entity = $this->resources->get($this->route);

            // early listeners @ pre-entity
            $this->entity->hook('entity', 'early');

            // set the results -- TODO: create a Response results obj
            $this->results = $this->entity->call($this->route);

            // late listeners @ post-entity
            $this->entity->hook('entity', 'late');

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

            // listeners @ server exception stage
            $this->hook('server', 'exception');
        }

        switch ($this->response->getHttpCode()) {
            case 401;
                // $this->response->setHeader('WWW-Authenticate',
                //    sprintf( '%s realm="%s"',
                //             $this->config['auth']['type'],
                //             $this->config['org']
                //     )
                // );
            break;

            case 405:
                $this->response->setHeader('Allow',
                    implode(', ', array_keys(
                        $this->entity->getAllActions()
                    )),
                    false // preserve existing
                );

        }

        $this->response->generate(
            $this->results,
            $this->getServerVersion($this->config),
            $this->config['output_rootNode']
        );

        // late listeners @ post-server
        $this->hook('server', 'late');

        return $this->request->getMethod() == 'HEAD'
                        ? null
                        : $this->response->getOutput();
    }

   /**
    * Gets the server version string.
    *
    * @return string
    */
    public function getServerVersion(array $config)
    {
        return sprintf('%s/%s (%s)',
            $config['api_realm'],
            $config['api_version'],
            Server::VERSION
        );
    }

   /**
    * Sets and initialise the routing processes.
    *
    * @param  Request $request
    *
    * @return void
    */
    public function setRouting(
        Request $request, array $resources, array $opts=null
    ) {
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
    * Returns the response object.
    *
    * @return Response
    */
    public function getResponse()
    {
        return $this->response;
    }


   /**
    * Returns the output format from the request chain.
    *
    * @param array $opts    Options are:
    *                          - [default] => string e.g. 'json',
    *                          - [controller_ext] => boolean,
    *                          - [override] => false or $_REQUEST['format'],
    *                          - [http_accept] => boolean.
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

}
