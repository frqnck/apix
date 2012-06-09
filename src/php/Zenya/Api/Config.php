<?php

namespace Zenya\Api;

class Config #extends Pimple
{

    protected $default = array(
        'org' => 'Zenya',
        'version'   => '1.0',

        // output
        'rootNode' => 'zenya',
        'sign'  => true,
        'debug' => true,

        // routes
        'route_prefix' => '@^(/index.php)?/api/v(\d*)@i', // regex
        
        'routes' => array(
            #'/:controller/paramName/:paramName/:id' => array(),
            #'/:controller/test' => array('class_name'=>'test'),

           '/help/:resource/:http_method/:filters' => array(
                'controller' => 'help',
                #'method'=>'GET'
            ),

            '/category/:param1/:param2/:param3' => array(
                'controller' => 'Category',
            ),

            '/:controller/:param1/:param2' => array(
                #'controller' => 'BlankResource',
                #'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                'class_args' => array('classArg1' => 'test1', 'classArg2' => 'test2'))
        ),

        // need a DIC !
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
        'resources_default' => array(
            // OPTIONS
            'help' => array(
                    'class_name'    => 'Zenya\Api\Resource\Help',
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

    private $injected = array();

    public function __construct(array $config=array())
    {
        $this->config = $this->default+$config;
        #Server::d($this);
    }

    public function injet($key, $mixed)
    {
        return $this->injected[$key] = $mixed;
    }

    public function getConfig()
    {
        #$this->config['resources_default']['help']['class_args'] = $this->injected['server'];
        return $this->config;
    }

}
