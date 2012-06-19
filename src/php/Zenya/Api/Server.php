<?php

namespace Zenya\Api;

/**
 * Temp Debug
 */
function d($mix)
{
    echo '<pre>';
    print_r($mix);
    echo '</pre>';
}

class Server extends Listener
{
    const VERSION = '@package_version@'; //'Sleepover/0.2.12';

    private $config = array();
    private $resources = array();

    public $route = null;

    public function __construct(Config $config=null, Request $request=null)
    {
        $c = $config === null ? Config::getInstance() : $config;

        $this->config = $c->getConfig();
        $this->request = $request === null ? Request::getInstance() : $request;

        // Response
        $this->response = new Response(
            $this->request,
            $this->config['output_sign'],
            $this->config['output_debug']
        );

        // Routing
        $this->setRouting(
            $this->request,
            $c->getRoutes(),
            $this->config['routing']
        );

        $defaultRoute = array(
            'class_name' => '\stdClass',
            'class_args' => $this->route->class_args
        );

        // to be passed thru the constructor!!!
        #$this->setconfig['resources'] = array(
#        );

        #d( $config->getResources() );

        // add the resources
        foreach ($c->getResources() as $key => $values) {
            $this->addResource($key, $values, $defaultRoute);
        }

        set_error_handler(array('Zenya\Api\Exception', 'errorHandler'));
        register_shutdown_function(array('Zenya\Api\Exception', 'shutdownHandler'));
    }

    public function run()
    {

        try {

            // if ($c['format_negotiation']['http_accept']) {
            //     // $this->response->setHeader('Vary', 'Accept');
            // }

            // attach the early listeners @ pre-processing stage
            $this->addAllListeners('server', 'early', $this->config);

            // Process with the requested resource
            $this->resource = new Resource($this->route);

            #d($this->request->getBody());

            $this->results = $this->resource->call(
                $this->getResource(
                    $this->route->getControllerName()
                )
            );

        } catch (\Exception $e) {

            $httpCode =  $e->getCode()>199 ? $e->getCode() : 500;
            $this->response->setHttpCode($httpCode);

            $this->results['error'] = array(
                'message'   => $e->getMessage(),
                'code'      => $httpCode
            );

            // set the error controller!
            if ( !in_array($this->route->getControllerName(), array_keys($this->getResources())) ) {
                $this->route->setControllerName('error');
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
                    implode(', ', array_keys($this->resource->actions)),
                    false // preserve existing
                );
        }

        $output = $this->response->generate(
                $this->rawControllerName, #$this->route->getControllerName(),
                $this->results,
                $this->getServerVersion(),
                $this->config['output_rootNode']
            );

        // attach the late listeners @ post-processing stage
        $this->addAllListeners('server', 'late');

        return $this->route->getMethod() != 'HEAD' ? $output : null;
    }

    private function getServerVersion()
    {
        return sprintf("%s/%s (%s)", $this->config['api_realm'], $this->config['api_version'], Server::VERSION);
    }

    /**
     * Get the output/results.
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    // /**
    //  * Get the output/results.
    //  *
    //  * @return array
    //  */
    // public function getConfig()
    // {
    //     return $this->config;
    // }

    /**
     * Set the route.
     *
     * @param  Request $request
     * @return void
     */
    public function setRouting(Request $request, array $routes, array $opts)
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
        } else {
            $ext = null;
        }

        $this->rawControllerName = $info['filename'];

        $this->route = new Router(
            $routes,
            array(
                'method'        => $request->getMethod(),
                'path'          => $path,
                'class_name'    => null,
                'class_args'    => &$this, // temp!
            )
        );
        $this->route->request = $request;

        // Set the response format...
        $this->negotiateFormat($opts, $ext);

        $this->route->map($path, $request->getParams());
    }

    /**
     * Returns the output format from an HTTP Accept.
     *
     * @param  Request $request
     * @return string  The output format
     */
    public function getFormatFromHttpAccept(Request $request)
    {
        if ($request->hasHeader('HTTP_ACCEPT')) {
            $accept = $request->getHeader('HTTP_ACCEPT');

            switch (true) {
                // 'application/json'
                case (strstr($accept, '/json')):
                    $format ='json';
                break;

                // 'text/xml', 'application/xml'
                case (strstr($accept, '/xml')
                    && (!strstr($accept, 'html'))):
                    $format = 'xml';
            }
        }

        return isset($format) ? $format : false;
    }

    /**
     * Returns the output format from the request chain.
     * @param array $opts Options are:
     *                      - [default] => string e.g. 'json',
     *                      - [controller_ext] => boolean,
     *                      - [override] => false or string use $_REQUEST['format'],
     *                      - [http_accept] => boolean.
     * @return string
     */
    public function negotiateFormat(array $opts, $extension=false)
    {
        switch (true) {
            case $opts['controller_ext']
                && $format = $extension:
            break;

            case false !== $opts['format_override']
                && $format = $opts['format_override']:
            break;

            case $opts['http_accept']
                && $format = $this->getFormatFromHttpAccept(
                        $this->request
                    ):
                $this->response->setHeader('Vary', 'Accept');
            break;

            default:
                $format = $opts['default_format'];
        }

        $this->response->setFormat($format, $opts['default_format']);
    }

    /**
     * Gets a ressource.
     *
     * @param  string                    $name A resource name
     * @return string
     * @throws \InvalidArgumentException 404
     */
    public function getResource($name)
    {
        if (!isset($this->resources[$name])) {
            $name = $this->rawControllerName;
            throw new \InvalidArgumentException(sprintf("Invalid resource's name specified (%s).", $name), 404);
        }

        return $this->resources[$name];
    }

    /**
     * Adds a resource, sanitize, etc...
     *
     * @param  string $name     The resource name
     * @param  array  $resource the resource array
     * @return void
     */
    public function addResource($name, array $resource, array $defaultClass=null)
    {
        if (! isset($resource['class_name']) ) {
            $resource['class_name'] = $defaultClass['class_name'];
            //throw new \InvalidArgumentException("todo: Resource missing a class name.", 500);
        }

        $class = new \stdClass;
        $class->name = $resource['class_name'];

        $class->args = isset($resource['class_args'])
            ? $resource['class_args']    // use provided
            : $defaultClass['class_args']; // use route's default

        $this->resources[$name] = $class;
    }

    /**
     * Gets all the resources.
     *
     * @return array An array of resources
     */
    public function getResources()
    {
        return $this->resources;
    }

}
