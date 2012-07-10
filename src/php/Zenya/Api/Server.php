<?php

namespace Zenya\Api;

use Zenya\Api\Listener,
    Zenya\Api\Config,
    Zenya\Api\Resources,
    Zenya\Api\Entity,
    Zenya\Api\Input,
    Zenya\Api\Request,
    Zenya\Api\Response;

class Server extends Listener
{
    const VERSION = '@package_version@';

    private $config = array();

    public $route = null;

    public function __construct($config=null, Request $request=null, Response $response=null)
    {
        // Set the config
        $c = $config instanceOf Config ? $config : Config::getInstance($config);
        $this->config = $c->get();

        // TEMP
        $c->inject('Server', $this);

        // Set the request
        $this->request = $request === null ? Request::getInstance() : $request;

        // Init response object
        $this->response =
            $response !== null
                ? $response
                : new Response(
                    $this->request,
                    $this->config['output_sign'],
                    $this->config['output_debug']
                );
        $this->response->setFormats($this->config['routing']['formats']);

        // set the resources
        $this->resources = new Resources;

        // set the generic errors & exception handlers
        set_error_handler(array('Zenya\Api\Exception', 'errorHandler'), E_ALL);
        register_shutdown_function(array('Zenya\Api\Exception', 'shutdownHandler'));
    }

    /**
    * @codeCoverageIgnore
    * @throws \InvalidArgumentException 404
    */
    public function run()
    {

        $c = Config::getInstance();

        // add all the resources from config.
        foreach ($c->getResources() as $key => $values) {
            $this->resources->add(
                $key, $values
            );
        }

        try {
            // set the routing
            $this->setRouting(
                $this->request,
                $this->resources->toArray(),
                $this->config['routing']
            );

            // set http accept
            if ($this->config['routing']['http_accept']) {
                $this->response->setHeader('Vary', 'Accept');
            }

            // attach the early listeners @ pre-processing stage
            $this->addAllListeners('server', 'early');

            // get the entity object
            $entity = $this->resources->get(
                $this->route
            );

            // attach the early listeners @ pre-processing stage
            $entity->addAllListeners('entity', 'early');

            $this->results = $entity->call();

            // attach the late listeners @ post-processing stage
            $entity->addAllListeners('entity', 'late');

        } catch (\Exception $e) {

            $http_code =  $e->getCode()>199 ? $e->getCode() : 500;
            $this->response->setHttpCode($http_code);

            $this->results['error'] = array(
                'message'   => $e->getMessage(),
                'code'      => $http_code
            );

            // set the error controller!
            if ( !in_array($this->route->getController(), array_keys( $this->resources->toArray() )) ) {
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
                    implode(', ', array_keys($entity->getActions())),
                    false // preserve existing
                );

        }

        $output = $this->response->generate(
                $this->route->getController(),
                $this->results,
                $this->getServerVersion($this->config['api_realm'], $this->config['api_version']),
                $this->config['output_rootNode']
            );

        // attach the late listeners @ post-processing stage
        $this->addAllListeners('server', 'late');

        return $this->route->getMethod() != 'HEAD' ? $output : null;
    }

    /**
     * Gets the server version string.
     *
     * @return string
     * @codeCoverageIgnore
     */
    private function getServerVersion($realm, $version)
    {
        return sprintf('%s/%s (%s)', $realm, $version, Server::VERSION);
    }

    /**
     * Sets the router.
     *
     * @param  Request $request
     * @return void
     */
    public function setRouting(Request $request, array $resources, array $opts)
    {
        // Get path without the route prefix
        $path = preg_replace($opts['route_prefix'], '', $request->getUri());

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
                'method'            => $request->getMethod(),
                'path'              => $path,
                'controller_name'   => null,    // TODO: TEMP!?
                'controller_args'   => &$this,  // TODO: TEMP!?
            )
        );

        // Set the response format...
        $this->negotiateFormat($opts, isset($ext)?$ext:false);

        $this->route->map($path, $request->getParams());

        if (isset($rawController)) {
            $this->route->setController($rawController);
        }
    }

    /**
     * Returns the output format from the request chain.
     *
     * @param  array $opts          Options are:
     *                              - [default] => string e.g. 'json',
     *                              - [controller_ext] => boolean,
     *                              - [override] => false or string use $_REQUEST['format'],
     *                              - [http_accept] => boolean.
     * @param  string|false $ext    The contoller defined extension.
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
                && $format = Input::getAcceptFormat($this->request):
                $this->response->setHeader('Vary', 'Accept');
            break;

            default:
                $format = $opts['default_format'];
        }

        $this->response->setFormat($format, $opts['default_format']);
    }


/* -- Closure prototyping  --- */



    protected function proxy($path, \Closure $to, $method)
    {
        return $this->resources->add($path,
            array(
                'action' => $to,
                'method' => $method
            )
        );
    }

    /**
     * POST request handler
     *
     * @param string $path Matched route pattern
     * @param mixed  $to   Callback that returns the response when matched
     *
     * @return Controller
     */
    public function onCreate($path, $to)
    {
        return $this->proxy($path, $to, 'POST');
    }

    public function onRead($path, $to)
    {
        return $this->proxy($path, $to, 'GET');
    }

    public function onUpdate($path, $to)
    {
        return $this->proxy($path, $to, 'PUT');
    }

    public function onModify($path, $to)
    {
        return $this->proxy($path, $to, 'PATCH');
    }

    public function onDelete($path, $to)
    {
        return $this->proxy($path, $to, 'DELETE');
    }

    public function onHelp($path, $to)
    {
        return $this->proxy($path, $to, 'OPTIONS');
    }

    public function onTest($path, $to)
    {
        return $this->proxy($path, $to, 'HEAD');
    }

    public function getBodyData()
    {
        return Input::getBodyData($this->request);
    }

    /**
     * test chain.
     * @param array $opts Options are:
     *
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

}

/**
 * Temp Debug
 */
function d($mix)
{
    echo '<pre>' . $mix . '</pre>';
}
