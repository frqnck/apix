<?php

namespace Zenya\Api;

class Container
{

    protected $params = array();

    public function __set($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function __get($key)
    {
        if (!isset($this->params[$key])) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not defined.', $key));
        } if (is_callable($this->params[$key])) {
            return $this->params[$key]($this);
        } else {
            return $this->params[$key];
        }
    }

    public function asShared($callable)
    {
        return function ($c) use ($callable) {
                    static $object;
                    if (is_null($object)) {
                        $object = $callable($c);
                    }

                    return $object;
                };
    }

}

class Server extends Listener
{
    public $debug = true;

    public $org = 'zenya';
    public $rootNode = 'zenya';
    public $version = 'Zenya/0.2.1';

    public $httpCode = 200;

    protected $resources = array();

    public function __construct() //array $resources)
    {

        // to be passed thru the constructor!!!
        $resources = array(
            'BlankResource' => array('class'=>'Zenya\Api\Resource\BlankResource', 'args'=>array('test')),

            'Category' => array(
                'class'=>'Zenya\Api\Resource\CategoryResource',
                'args'=>array('test')
            ),
        );

       $config = array(
            'org' => "Zenya",

            'route_prefix' => '@^(/index.php)?/api/v(\d*)@i', // regex

            // routes
            'routes' => array(
                #'/:controller/paramName/:paramName/:id' => array(),
                #'/:controller/test' => array('class'=>'test'),

                '/category/:param1/:param2/:param3' => array(
                    'controller' => 'Category',

                ),


                '/:controller/:param1/:param2' => array(
                    #'controller' => 'BlankResource',
                    #'className' => 'Zenya\Api\Resource\BlankResource',
                    'classArgs' => array('classArg1' => 'test1', 'classArg2' => 'test2'))
            ),

            // need DIC here!!
            'listeners' => array(
                // pre-processing stage
                'early' => array(
                    'new Listener\Auth',
                    'new Listener\Acl',
                    'new Listener\Log',
                    'new Listener\Mock'
                ),

                // post-processing stage
                'late'=>array(
                    'new Listener\Log',
                ),
            ),

            // -- advanced options --
            'auth' => array(
                    'type'=>'Basic',
                    #'type'=>'Digest',
                ),
            'internals' => array(
                'HTTP_OPTIONS' => array(
                        'class'     =>  'Zenya\Api\Resource\Help',
                        #'classArgs'    => array('resource' => &$this),
                        'args'      => array('params'=>'dd')
                    ),
                'HTTP_HEAD' => array(
                        'class'     => 'Zenya\Api\Resource\Test',
                        'classArgs' => array('resource' => $this),
                        'args'      => array()
                    )
            )
        );

        $this->config = $config;

        $this->resources = $resources+$this->config['internals'];

        set_error_handler(array('Zenya\Api\Exception', 'errorHandler'));
        register_shutdown_function(array('Zenya\Api\Exception', 'shutdownHandler'));
    }

    /**
     * Gets a ressource.
     *
     * @param string $name A resource name
     */
    public function getResource($name)
    {
        if (!isset($this->resources[$name])) {
            throw new \InvalidArgumentException(sprintf("Invalid resource's name specified (%s).", $name), 404);
        }

        return $this->resources[$name];
    }

    /**
     * Adds a resource.
     *
     * @param Resource $resource A resource object
     */
    public function addResource(Resource $resource)
    {
        $resource = new Resource();
        $this->resources[$resource->getName()] = $resource;
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



    public function run()
    {
        $config = $this->config;

        // Request
        $this->request = new Request;

        // get path without the route prefix
        $path = preg_replace($config['route_prefix'], '', $this->request->getUri());

        // Routing
        $this->route = new Router($config['routes'], array(
            'method'    => $this->request->getMethod(),
            'path'      => $path,
            'className' => null,
            'classArgs' => null
        ));

        $this->response = new Response($this);

        try {

            $this->route->map($path);

            $name = explode('.', $this->route->controller);
            $this->route->name = $name[0];
            $this->route->format = count($name)>1 ? end($name) : null;
            $this->route->params = $this->route->params+$this->request->getParams();

            // set format from extension then from html head
            if (isset($this->route->format)) {
                $format = $this->route->format;
            } elseif (isset($_GET['format'])) {
                $format = $_GET['format'];
            } else {
                if($this->request->hasHeader('HTTP_ACCEPT')) {
                    $this->response->setHeader('Vary', 'Accept');
                }
                $accept = $this->request->getHeader('HTTP_ACCEPT');
                switch (true) {

                    // 'application/json'
                    case (strstr($accept, '/json')):
                        $format = 'json';
                    break;

                    // 'text/xml', 'application/xml'
                    case (strstr($accept, '/xml')
                        && (!strstr($accept, 'html'))):
                        $format = 'xml';
                    break;

                    default:
                        $format = Response::DEFAULT_FORMAT;
                }
            }

            // Set and sanittize the Response format...
            $this->response->setFormat($format);

            // attach early listeners @ pre-processing
            $this->stage = 'early';
            $this->addAllListeners('server', 'early');
            $this->stage = 'late';

            // Process with the requested resource
            #  $resource = $this->getResource($this->route->name);
            $this->resource = new Resource($this);

            $this->results = $this->resource->call();

        } catch (\Exception $e) {
            $this->results = array(
                'error' => $e->getMessage(),
            );
            $this->httpCode = $e->getCode() ? $e->getCode() : 500;

            // attach late listeners @ exceptions
            $this->addAllListeners('server', 'exception');
        }

        switch ($this->httpCode) {
            case 401;
                #$this->response->setHeader('WWW-Authenticate',
                #    sprintf('%s realm="%s"', $this->config['auth']['type'], $this->config['org'])
                #);
            break;

            case 405:
                $this->response->setHeader('Allow',
                    implode(', ', $this->resource->getMethods())
                );
        }

        $withBody = $this->route->method != 'HEAD';
        return $this->response->send( $withBody);

        // attach late listeners @ post-processing
        $this->addAllListeners('server', 'late');

        return $body;
    }

    public static function d($mix)
    {
        echo '<pre>';
        print_r($mix);
        echo '</pre>';
    }

}
