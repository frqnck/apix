<?php

namespace Zenya\Api;

class Server extends Listener
{
    const VERSION = 'Sleepover/0.2.11';

    private $config = array();
    private $resources = array();

    public $route = null;

    public function __construct(array $resources=null, array $config=array())
    {

        $c = new Config($config);
        #$c->injet('server', $this);
        $this->config = $c->getConfig();

        // to be passed thru the constructor!!!
        $resources = array(

            // 'test' => array(
            //     'class_args'=>array('arg1'=>'value1', 'arg2'=>'string')
            // ),

            'resourceName' => array(
                'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
            ),

            'someName' => array(
                'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                #'class_args' => array('test')
            )

        );

        $this->config['resources'] = $resources;

        $this->request = new Request;

        $this->response = new Response(
            $this->request,
            $this->config['sign'],
            $this->config['debug']
        );

        set_error_handler(array('Zenya\Api\Exception', 'errorHandler'));
        register_shutdown_function(array('Zenya\Api\Exception', 'shutdownHandler'));
    }

    public function run()
    {
        $c = &$this->config;

        // Routing
        $this->setRouting($this->request);

        $defaultRoute = array(
            'class_name'=>'\stdClass',
            'class_args' => $this->route->class_args
        );

        // add the resources
        foreach ($c['resources']+$c['resources_default'] as $key => $values) {
            $this->addResource($key, $values, $defaultRoute);
        }

        try {

            // if ($c['format_negotiation']['http_accept']) {
            //     // $this->response->setHeader('Vary', 'Accept');
            // }

            // attach the early listeners @ pre-processing stage
            $this->addAllListeners('server', 'early');

            // Process with the requested resource
            $this->resource = new Resource($this->route);

            $this->results = $this->resource->call(
                $this->getResource(
                    $this->route->getControllerName()
                )
            );

        } catch (\Exception $e) {

            if ( !in_array($this->route->getControllerName(), array_keys($this->getResources())) ) {
                $this->route->setControllerName('error');
                $this->results[] = $e->getMessage();
            } else {
                $this->results['error'] = $e->getMessage();
            }

            $this->response->setHttpCode(
                $e->getCode()>199 ? $e->getCode() : 500
            );

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
                    $this->route->getControllerName(),
                    $this->results,
                    sprintf("%s/%s #%s", $this->config['org'], $this->config['version'], self::VERSION),
                    $this->config['rootNode']
                );

        // attach the late listeners @ post-processing stage
        $this->addAllListeners('server', 'late');

        return $this->route->getMethod() != 'HEAD' ? $output : null;
    }

    /**
     * Set the route.
     *
     * @param  Request $request
     * @return void
     */
    public function setRouting(Request $request)
    {

        echo 'dd' . $request->getUri() . 'dd';exit;
        // Get path without the route prefix
        $path = preg_replace($this->config['route_prefix'], '', $request->getUri());

        $this->route = new Router(
            $this->config['routes'],
            array(
                'method'        => $request->getMethod(),
                'path'          => $path,
                'class_name'    => null,
                'class_args'    => &$this, // temp!
            )
        );

        // check extension
        if($this->config['format_negotiation']['controller_ext']) {
            $parts = explode('/', $path);
            $ext = pathinfo($parts[1], PATHINFO_EXTENSION);
            if($ext != null) {
                $path = preg_replace('/\.' . $ext . '/', '', $path, 1);
            }
        }

        // Set the response format...
        $this->negotiateFormat($this->config['format_negotiation'], $ext);

        $this->route->map($path, $request->getParams());
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

            case false !== $opts['override']
                && $format = $opts['override']:
            break;

            case $opts['http_accept']
                && $format = $this->getFormatFromHttpAccept(
                        $this->request
                    ):
                $this->response->setHeader('Vary', 'Accept');
            break;

            default:
                $format = $opts['default'];
        }

        $this->response->setFormat($format, $opts['default']);
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
     * Get the output/results.
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
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

    /**
     * Temp Debug
     */
    public static function d($mix)
    {
        echo '<pre>';
        print_r($mix);
        echo '</pre>';
    }

}
