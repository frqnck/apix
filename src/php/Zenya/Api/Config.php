<?php

namespace Zenya\Api;

class Config extends \Pimple
{

    public $config = array();
    private $injected = array();

    /**
     * The singleton instance
     * @var ExtensionGuesser
     */
    static private $instance = null;

    /**
     * Returns as a singleton instance
     *
     * @return ExtensionGuesser
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
        $app = $this;

        $app['config_file'] = getenv('HOME') . '/.zenya/config.php';

        $this['test'] = 'test';

        $this['server_debug'] = 'test';
        #$this['server_config'] = $config+$this->getDefaults();

        #$this['server_version'] = function($app) {};
        #sprintf("%s/%s #%s", $this->config['server_realm'], $this->config['server_version'], self::VERSION),

        $this->config = $this->getConfigurations();

        $this->services = $this->getServices();
print_r(
    $this->services['users']('franck', 'sleepover.dev')
);

echo '_Construct Once';

    }

    public function getConfigurations()
    {
        if (is_file($this['config_file'])) {
            $config = require $this['config_file'];
            if (null === $config || !is_array($config)) {
                throw new \RuntimeException(sprintf('The "%s" configuration file must return an array.', $app['config_file']));
            }
        }

        // merge
        return $config+$this->getConfigDefaults();
    }

    public function getServices($key=null)
    {
        $config = $this->config['services'];
        if (is_null($key)) {
            return $config;
        } elseif (isset($config[$key])) {
            return $config[$key];
        }
       throw new \InvalidArgumentException( sprintf('Services for "%s" does not exists.', $key) );
    }

    public function getListeners($key=null)
    {
        $config = $this->config['listeners'];
        if (is_null($key)) {
            return $config;
        } elseif (isset($config[$key])) {
            return $config[$key];
        }
       throw new \InvalidArgumentException( sprintf('Listeners for "%s" does not exists.', $key) );
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
            'routes_default' => array(),

            // listeners
            'listeners' => array(
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
                        'Zenya\Api\Listener\Auth' => function()
                        {



                            return new \Zenya\Api\Listener\Auth\HttpDigest();
                        },

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

            // auth shoud be in listeners (todo: DIC)
            'auth' => array(
                    'type'=>'Basic',
                    #'type'=>'Digest',
                ),

            // resources user defined (move out)
            'resources' => array(
                // 'test' => array(
                //     'class_args'=>array('arg1'=>'value1', 'arg2'=>'string')
                // ),
                'resourceName' => array(
                    'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                    'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
                ),
                'AuthResource' => array(
                    'class_name' => 'Zenya\Api\Fixtures\AuthResource',
                    'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
                ),

                'someName' => array(
                    'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                    #'class_args' => array('test')
                )
            ),

            // resources minimal
            #'resources' => array(),

            // default
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