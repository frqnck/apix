<?php

namespace Zenya\Api;

class Config #extends \Pimple
{

    private $config = array();
    private $injected = array();

    public function __construct(array $config=array())
    {
        $this->config = $config+$this->getDefaults();
        #Server::d($this->config);

        #print_r($this->config['resources']);

    }

    public function getConfig($key=null)
    {
        if(is_null($key)) {
            return $this->config;
        } else if(isset($this->config[$key])) {
            return $this->config[$key];
        } else {
            throw new \InvalidArgumentException(sprintf('Config for "%s" does not exists.', $key));
        }
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




    public function getDefaults()
    {
        return array(
            'org' => 'Zenya',
            'version'   => '1.0',

            // output
            'rootNode'  => 'zenya',
            'sign'      => true,
            'debug'     => true,

            // routing
            'options' => array(
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

            // auth shoud be in listeners (todo: DIC)
            'auth' => array(
                    'type'=>'Basic',
                    #'type'=>'Digest',
                ),

            // resources definition
            'resources' => array(            // 'test' => array(
            //     'class_args'=>array('arg1'=>'value1', 'arg2'=>'string')
            // ),

            'resourceName' => array(
                'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                'class_args' => array('arg1'=>'value1', 'arg2'=>'string')
            ),

            'someName' => array(
                'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                #'class_args' => array('test')
            )), // user defined

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
