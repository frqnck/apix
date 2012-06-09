<?php

namespace Zenya\Api;

class Config #extends Pimple
{

    protected $config = array(
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
                    #'className' => 'Zenya\Api\Fixtures\BlankResource',
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
                        'classArgs' => '&$this',
                        'args'      => array('params'=>'dd')
                    ),
                // HEAD
                'test' => array(
                        'class'     => 'Zenya\Api\Resource\Test',
                        'classArgs' => '&$this',
                        'args'      => array()
                    )
            )
        );

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
