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
        // to pass in the constructor!!!
        $resources = array('BlankResource' => array('class'=>'Zenya\Api\Resource\BlankResource', 'args'=>array('test')));

        // from config
        $internals = array(
            'HTTP_OPTIONS' => array(
                    'class'     =>  'Zenya\Api\Resource\Help',
                    #'classArgs'    => array('resource' => &$this),
                    'args'      => array('params'=>'dd')
                ),
            'HTTP_HEAD' => array(
                    'class'     => 'Zenya\Api\Resource\Test',
                    'classArgs' => array('resource' => &$this),
                    'args'      => array()
                )
        );

        $this->resources = $resources+$internals;
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
        $config = array(
            'route_prefix' => '@^(/index.php)?/api/v(\d*)@i', // regex
            'routes' => array(
                #'/:controller/paramName/:paramName/:id' => array(),
                #'/:controller/test' => array('class'=>'test'),
                '/:controller/:param1/:param2' => array(
                    #'controller' => 'BlankResource',
                    'className' => 'Zenya\Api\Resource\BlankResource',
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
            )
        );

        try {
            // Request
            $this->request = new Request;

            // get path without the route prefix
            $path = preg_replace($config['route_prefix'], '', $this->request->getUri());

            // Routing
            $this->route = new Router($config['routes'], array(
                'method' 	=> $this->request->getMethod(),
                'path'	 	=> $path,
                'className'	=> null,
                'classArgs'	=> null
            ));

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
                $accept = $this->request->getHeader('Accept');
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

            $this->route->format = $format;

            // TODO: fix this!
            Response::throwExceptionIfUnsupportedFormat($format);

            // attach early listeners @ pre-processing
            $this->stage = 'early';
            $this->addAllListeners('server', 'early');
            $this->stage = 'late';

            // Process with the requested resource
            ###$resource = $this->getResource($this->route->name);
            $resource = new Resource($this);
            $this->results = $resource->call();
           # print_r($resource);exit;


        } catch (\Exception $e) {
            $this->results = array(
                'error' => $e->getMessage(),
            );
            $this->httpCode = $e->getCode() ? $e->getCode() : 500;

            // attach late listeners @ exceptions
            $this->addAllListeners('server', 'exception');
        }

        $response = new Response($this, $this->route->format);
        echo $response->send($resource, $this->route->method);

        // attach late listeners @ post-processing
        $this->addAllListeners('server', 'late');
    }

    public static function d($mix)
    {
        echo '<pre>';
        print_r($mix);
        echo '</pre>';
    }

}
