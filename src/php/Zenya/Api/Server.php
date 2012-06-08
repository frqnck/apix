<?php

namespace Zenya\Api;

class Server extends Listener
{
    public $org = 'zenya';
    public $rootNode = 'zenya';
    public $version = 'Zenya/0.2.1';

    public $httpCode = 200;

    protected $resources = array();

    public function __construct(array $resources=null)
    {

        // to be passed thru the constructor!!!
        $resources = array(
            'BlankResource' => array('class'=>'Zenya\Api\Resource\BlankResource', 'classArgs'=>array('arg1'=>'value1', 'string')),

            'Category' => array(
                'class'=>'Zenya\Api\Resource\CategoryResource',
                'classArgs'=>array('test')
            ),
        );

       $config = array(
            'org' => "Zenya",
            'debug' => true,
            'sign'  => true,
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
                // OPTIONS
                'help' => array(
                        'class'     => 'Zenya\Api\Resource\Help',
                        'classArgs' => &$this,
                        'args'      => array('params'=>'dd')
                    ),
                // HEAD
                'test' => array(
                        'class'     => 'Zenya\Api\Resource\Test',
                        'classArgs' => &$this,
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

        $this->response = new Response($this->request, $this->config['sign'], $this->config['debug']);

        try {

            $this->route->map($path, $this->request->getParams());
            $name = explode('.', $this->route->getControllerName());
            $this->route->controller = $name[0];
            
            // set format first fromcontroller extension
            
            if (  count($name)>1 && end($name)!=null) {
                $format = end($name);
            } elseif (isset($_GET['format'])) { // or from GET['format']
                $format = $_GET['format'];
            } else {    // or from HTTP header

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
                       $format = null;
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
            $this->response->setHttpCode($e->getCode() ? $e->getCode() : 500);

            // attach late listeners @ exceptions
            $this->addAllListeners('server', 'exception');
        }

        switch ($this->response->getHttpCode()) {
            case 401;
                #$this->response->setHeader('WWW-Authenticate',
                #    sprintf('%s realm="%s"', $this->config['auth']['type'], $this->config['org'])
                #);
            break;

            case 405:
                $this->response->setHeader('Allow',
                    implode(', ', $this->resource->getMethods($this->route))
                );
        }

        $output = $this->response->generate(
                    $this->route->getControllerName(),
                    $this->results,
                    $this->version,
                    $this->rootNode
                );

        // attach late listeners @ post-processing
        $this->addAllListeners('server', 'late');

        return $this->route->getMethod() != 'HEAD' ? $output : null;
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

    public static function d($mix)
    {
        echo '<pre>';
        print_r($mix);
        echo '</pre>';
    }

}
