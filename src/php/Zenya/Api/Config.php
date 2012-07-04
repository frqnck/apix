<?php

namespace Zenya\Api;

class Config extends \Pimple
{

    public $config = array();
    private $injected = array();

    /**
     * The singleton instance
     * @var Config
     */
    static private $instance = null;

    /**
     * Returns as a singleton instance
     *
     * @return Config
     */
    static public function getInstance($skip=false)
    {
        if (null === self::$instance) {
            self::$instance = new self($skip);
        }

        return self::$instance;
    }

    public function __construct($skip=false)
    {
        $c = $this;

        $this['server_debug'] = 'test';

        $file = realpath(__DIR__ . '/../../../data/config.dist.php');
        // echo $file = realpath('../data/config.dist.php');
        //getenv('HOME') . '/.zenya/config.php';
        ##$file = realpath('../data/config.dist.php');

        if( $skip !== true ) {
            $this->config = $this->getConfigurations($file);
        } else {
            $this->config = $this->getConfigDefaults();
        }
        // TODO: debug
        //echo ' [construct] ';
    }

    public function getConfigurations($file)
    {
        if (is_file($file)) {
            $config = require $file;
            if (null === $config || !is_array($config)) {
                throw new \RuntimeException(sprintf('The "%s" configuration file must return an array.', $file));
            }
            // merge
            return $config+$this->getConfigDefaults();
        } else {
            throw new \RuntimeException(sprintf('The "%s" configuration file does not exist.', $file));
        }
    }

    public function getServices($key=null)
    {
        $cb = $this->retrieve('services', $key);
        #$shared = $this->share($cb);

        return $cb();
    }

    public function getListeners($key=null)
    {
        return $this->retrieve('listeners', $key);
    }

    protected function retrieve($kind, $key=null)
    {
        $config = $this->config[$kind]+$this->config[$kind .'_default'];
        if (is_null($key)) {
            return $config;
        } elseif (isset($config[$key])) {
            return $config[$key];
        }
       throw new \RuntimeException( sprintf('%s for "%s" does not exists.', ucfirst($kind), $key) );
    }

    public function get($key=null)
    {
        if (is_null($key)) {
            return $this->config;
        } elseif (isset($this->config[$key])) {
            return $this->config[$key];
        }
       throw new \InvalidArgumentException( sprintf('Config for "%s" does not exists.', $key) );
    }

    public function getResources($key=null)
    {
        return $this->retrieve('resources', $key);
    }


    public function inject($key, $mixed)
    {
        return $this->injected[$key] = $mixed;
    }

    public function getInjected($key)
    {
        return $this->injected[$key];
    }

    // public function getRoutes()
    // {
    //     return $this->config['routes']+$this->config['routes_default'];
    // }

    // New: closure
    public function addRoute($route, $action)
    {
        if($action instanceOf \Closure) {
            return $this->config['routes'][$route] = array(
                'controller' => $route,
            );
        }

        throw RuntimeException('Route could not be imported');
    }


    public function getConfigDefaults()
    {
        #$c = $this;
        return array(
            'api_realm'     => 'Zenya',
            'api_version'   => '1.0',

            // output
            'output_rootNode'  => 'zenya',
            'output_sign'      => true,
            'output_debug'     => true,

            // routing
            'routing' => array(
                'route_prefix'      => '@^(/index(\d)?.php)?/api/v(\d*)@i', // regex
                'default_format'    => 'json',
                'formats'           => array('json', 'xml', 'html', 'php', 'jsonp'),
                // output format negociations
                'controller_ext'    => true, // true or false (e.g. resource.json)
                'format_override'   => isset($_REQUEST['format']) ? $_REQUEST['format'] : false,
                'http_accept'       => true, // true or false
            ),

            // services
            'services' => array(),
            'services_default' => array(),

            // listeners
            'listeners' => array(),
            'listeners_default' => array(
                'server' => array(
                    // pre-processing stage
                    'early' => array(
                        #'Zenya\Api\Listener\Mock',
                        #'Zenya\Api\Listener\BodyData',
                    ),
                    // post-processing stage
                    'late'=>array(
                    ),
                    // errors and exceptions
                    'exception' => array(
                        #'Zenya\Api\Listener\Log' => array('php://output'),
                    )
                ),
                // 'request' => array(
                //     'early'=>array(
                //         'Zenya\Api\Listener\BodyData',
                //     ),
                //     #'Zenya\Api\Listener\Log',
                // ),
                'entity' => array(
                    'early' => array(
                        // todo
                        /*
                        'Zenya\Api\Listener\Auth' => function()
                        {
                            #echo $c['api_realm'];
                            #"$c['api_realm']"
                            return new \Zenya\Api\Listener\Auth\HttpDigest();
                        },
*/
                        #'Zenya\Api\Listener\CheckIp' => null,
                        #'Zenya\Api\Listener\Acl',
                        #'Zenya\Api\Listener\Log',
                        #'Listener\Log',
                    ),
                    // post-processing stage
                    'late'=>array(

                        #'Zenya\Api\Listener\Mock',
                        #'Zenya\Api\Listener\Log' => array('php://output'),
                    ),
                ),
                'response' => array(),
            ),

            // resources
            'resources' => array(),
            'resources_default' => array(
                // OPTIONS
                'help' => array(
                    'controller' => array(
                        'name' => __NAMESPACE__ . '\Resource\Help',
                        'args' => null,
                        // 'args'          => array(
                        //     'method'    => 'GET',
                        //     'name'      => $this->route->getControllerName(),
                        //     'resource'  => $this->server->getResource( $route->getControllerName() ),
                        //     'params'    => $route->getParams(),
                        // )
                    ),
                ),
                // HEAD
                'test' => array(
                    'controller' => array(
                        'name' => __NAMESPACE__ . '\Resource\Test',
                        'args' => null #array( &$this ), #
                    ),
                    // 'args' => array()
                )
            )
        );
    }

}