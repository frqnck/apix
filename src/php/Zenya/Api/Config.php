<?php

namespace Zenya\Api;

class Config #extends Pimple
{

    private $config = array();
    private $injected = array();

    public function __construct(array $config=array())
    {
        $this->config = $config+$this->getDefaults();
        #Server::d($this->config);
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

    public function getDefaults()
    {
        return array(
            'org' => 'Zenya',
            'version'   => '1.0',

            // output
            'rootNode' => 'zenya',
            'sign'  => true,
            'debug' => true,

            // routes
            'route_prefix' => '@^(/index.php)?/api/v(\d*)@i', // regex

            'routes' => array(
                #/:controller/paramName/:paramName/:id' => array(),
                #'/:controller/test' => array('class_name'=>'test'),

                '/help/:resource/:http_method/:filters' => array(
                     'controller' => 'help',
                ),

                // '/category/:param1/:param2/:param3' => array(
                //     'controller' => 'Category',
                // ),

                '/:controller/:resource/:param2' => array(
                    #'controller' => 'BlankResource',
                    #'class_name' => 'Zenya\Api\Fixtures\BlankResource',
                    #'class_args' => array('classArg1' => 'test1', 'classArg2' => 'test2')
                )
            ),
            // format negociation
            'format_negotiation' => array(
                'default'        => 'json',
                'controller_ext' => true, // true or false (e.g. resource.json)
                'override'       => isset($_REQUEST['format']) ? $_REQUEST['format'] : false,
                'http_accept'    => true, // true or false
            ),

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
            'resources' => null, // user defined

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
