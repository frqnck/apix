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
    static public function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $c = $this;

        $this['server_debug'] = 'test';

        $this['config_file'] = realpath(__DIR__ . '/../../../data/config.dist.php');
            //getenv('HOME') . '/.zenya/config.php';

        $this->config = $this->getConfigurations();

#$users = $this->getServices('users');
#print_r($users);exit;

        #echo '<pre>'; print_r($this->getListeners()); exit;

        // TODO: debug
        //echo ' [construct] ';
    }

    public function getConfigurations()
    {
        if (is_file($this['config_file'])) {
            $config = require $this['config_file'];
            if (null === $config || !is_array($config)) {
                throw new \RuntimeException(sprintf('The "%s" configuration file must return an array.', $this['config_file']));
            }
            // merge
            return $config+$this->getConfigDefaults();
        } else {
            throw new \RuntimeException(sprintf('The "%s" configuration file does not exist.', $this['config_file']));
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


    public function getConfig($key=null)
    {
        if (is_null($key)) {
            return $this->config;
        } elseif (isset($this->config[$key])) {
            return $this->config[$key];
        }
       throw new \InvalidArgumentException( sprintf('Config for "%s" does not exists.', $key) );
    }

    public function getResources()
    {
        #$this->config['resources_default']['help']['class_args'] = $this->injected['server'];

        return $this->config['resources']+$this->config['resources_default'];
    }

    public function getRoutes()
    {
        return $this->config['routes']+$this->config['routes_default'];
    }

    public function injet($key, $mixed)
    {
        return $this->injected[$key] = $mixed;
    }

    public function getConfigDefaults()
    {
        $c = $this;
        return array(
            'api_realm'     => 'Zenya',
            'api_version'   => '1.0',

            #return sprintf("%s/%s #%s", $app->config['realm'], $app->config['version'], Server::VERSION);
            #'test'=> $this['debug'],

            // output
            'output_rootNode'  => 'zenya',
            'output_sign'      => true,
            'output_debug'     => true,

            // routing
            'routing' => array(
                'route_prefix'      => '@^(/index.php)?/api/v(\d*)@i', // regex
                'default_format'    => 'json',
                // following is use for output format negociation
                'controller_ext'    => true, // true or false (e.g. resource.json)
                'format_override'   => isset($_REQUEST['format']) ? $_REQUEST['format'] : false,
                'http_accept'       => true, // true or false
            ),

            //  routes
            'routes' => array(),
            'routes_default' => array(),

/*
            'routes' => array(
                #/:controller/paramName/:paramName/:id' => array(),
                #'/:controller/test' => array('class_name'=>'test'),

                '/help/:resource/:http_method/:filters' => array(
                     'controller' => 'help',
                ),

                // '/category/:param1/:param2/:param3' => array(
                //     'controller' => 'Category',
                // ),

                '/auth/:param1' => array(
                    'controller' => 'AuthResource'
                ),

                '/:controller/:param1/:param2' => array(
                    #function() {echo '------ss';},
                    #'controller' => 'BlankResource',
                    #'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                    #'class_args' => array('classArg1' => 'test1', 'classArg2' => 'test2')
                )
            ),
*/
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
                    ),
                    // post-processing stage
                    'late'=>array(),
                    // errors and exceptions
                    'exception' => array(
                        #'Zenya\Api\Listener\Log' => array('php://output'),
                    )
                ),
                'request' => array(
                    #'Zenya\Api\Listener\Log',
                ),
                'resource' => array(
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
                        'class_name'    => __NAMESPACE__ . '\Resource\Help',
                        'class_args'    => null, //&$this
                        'args'          => array(
                            # 'method'  => 'GET',
                            # 'name'    => $this->route->getControllerName(),
                            # 'resource'  => $this->server->getResource( $route->getControllerName() ),
                            # 'params'    => $route->getParams(),
                        )
                    ),
                // HEAD
                'test' => array(
                        'class_name'    => 'Zenya\Api\Resource\Test',
                        'class_args'    => null,
                        'args'          => array()
                    )
            )
        );
    }

}