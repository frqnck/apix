<?php

namespace Zenya\Api;

use Zenya\Api\Entity;

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
    const VERSION = '@package_version@';

    private $config = array();

    public $route = null;

    public function __construct(Config $config=null, Request $request=null, Response $response=null)
    {
        $c = $config === null ? Config::getInstance() : $config;

        $this->config = $c->get();

        $c->inject('Server', $this);

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

        // Init response object
        $this->resources = new Resources;
        #$this->resources->setEntity(new Entity());

        set_error_handler(array('Zenya\Api\Exception', 'errorHandler'));
        register_shutdown_function(array('Zenya\Api\Exception', 'shutdownHandler'));
    }

    /**
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

        // Routing
        $this->setRouting(
            $this->request,
            $this->resources->toArray(),
            $this->config['routing']
        );
 #   }
 ##   public function run()
 #   {

        try {

            // if ($c['format_negotiation']['http_accept']) {
            //  $this->response->setHeader('Vary', 'Accept');
            // }

            // attach the early listeners @ pre-processing stage
            $this->addAllListeners('server', 'early', $this->config);

            $name = $this->route->getPathName();
            if (!$this->resources->has($name)) {
                throw new \InvalidArgumentException(
                    sprintf("Invalid resource entity specified (%s).", $name), 404
                );
            }

            // Process the requested resource
            $resource = $this->resources->get($name);
            $this->results = $resource->call($this->route);

        } catch (\Exception $e) {

            $httpCode =  $e->getCode()>199 ? $e->getCode() : 500;
            $this->response->setHttpCode($httpCode);

            $this->results['error'] = array(
                'message'   => $e->getMessage(),
                'code'      => $httpCode
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
                    implode(', ', array_keys($resource->getActions())),
                    false // preserve existing
                );
        }

        $output = $this->response->generate(
                $this->rawControllerName, #$this->route->getController(),
                $this->results,
                $this->getServerVersion(),
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
     */
    private function getServerVersion()
    {
        return sprintf("%s/%s (%s)", $this->config['api_realm'], $this->config['api_version'], Server::VERSION);
    }

    /**
     * Gets the results array.
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
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
            $this->rawControllerName = $info['filename'];
        } else {
            $ext = null;
        }

        $this->route = new Router(
            $resources,
            array(
                'method'            => $request->getMethod(),
                'path'              => $path,
                'controller_name'   => null,
                'controller_args'   => &$this, // TODO: temp!'
            )
        );

        // TODO: modify this!!
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

    protected function proxy($path, \Closure $to, $method)
    {
        return $this->resources->add($path,
            array(
                'action' => $to,
                'method' => $method
            )
        );
    }

    public function onCreate($path, $to)
    {
        $this->proxy($path, $to, 'POST');
    }

    public function onRead($path, $to)
    {
        return $this->proxy($path, $to, 'GET');
    }

    public function onUpdate($path, $to)
    {
        $this->proxy($path, $to, 'PUT');
    }

    public function onModify($path, $to)
    {
        $this->proxy($path, $to, 'PATCH');
    }

    public function onDelete($path, $to)
    {
        $this->proxy($path, $to, 'DELETE');
    }

    public function onHelp($path, $to)
    {
        $this->proxy($path, $to, 'OPTIONS');
    }

    public function onTest($path, $to)
    {
        $this->proxy($path, $to, 'HEAD');
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