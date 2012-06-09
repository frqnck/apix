<?php

namespace Zenya\Api;

/**
 * Represents a resource.
 *
 */
class Resource extends Listener
{

    /**
     * Stores the resource methods.
     *
     * @var array
     */
    protected $methods = array();

    /**
     * Import given objects
     *
     * @param array $resources
     */
    public function __construct(Router $route)
    {
        $this->route = $route;

        // attach late listeners @ post-processing
        #$this->addAllListeners('resource', 'early');
    }

    /**
     * Return the classname for a resource (long and private)
     *
     * @param  Router $route
     * @return string
     */
    public function setRouteOverrides(Router $route)
    {
        switch ($route->getMethod()) {
            case 'OPTIONS': // resource's help
            case 'HEAD':    // resource's test
                $route->setControllerName($route->getMethod()=='OPTIONS' ? 'help' : 'test');

                $route->setParams(
                    array(
                      'resource'     => $route->getControllerName(),
                      'http_method'  => $route->hasParam('http_method') ? $route->getParam('http_method') : null,
                      #'optionals'   => new Request,
                      #'filters'     => 'itest'
                    )
                );
                #Server::d($route->getParams());

            break;
        }
    }

    /**
     * Call a resource from route
     *
     * @params Router	$route	Route object
     * @return array
     * @throws Zenya\Api\Exception
     */
    public function call(\stdClass $class)
    {
        $route = $this->route;

        $this->setRouteOverrides($route);

        // Relection
        $refClass = new ReflectionClass($class->name);
        $this->actions = $refClass->getActionsMethods($route->getActions());

// TODO: merge with TEST & OPTIONS ??? 
Server::d( $this->actions );

        // if( !in_array($route->getMethod(), array('OPTIONS')) )
        // {

            try {
                $refMethod = $refClass->getMethod($route->getAction());
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified.", 405);
            }

            $params = $this->getRequiredParams($route->getMethod(), $refMethod, $route->getParams());

        // } else { 
        //     $refMethod = $refClass->getMethod($route->getAction());
        //     $params = array();
        // }



        // TODO: maybe we need to check the order of params key match the method?
        
        // TODO: maybe add a type casting handler here
        #Server::d($route);exit;


        // attach late listeners @ post-processing

        // TODO: docs
        #$classDoc = RefDoc::parseDocBook($refClass);
        #$methodDoc = RefDoc::parseDocBook($refMethod);

        $this->addAllListeners('resource', 'early');

        return call_user_func_array(array(new $class->name($class->args), $route->getAction()), $params);
    }

    public function getRequiredParams($method, $refMethod, array $routeParams)
    {
        $params = array();
        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            if (
                !$param->isOptional()
                && !array_key_exists($name, $routeParams)
            ) {
                throw new \BadMethodCallException("Required {$method} parameter \"{$name}\" missing in action.", 400);
            } else if (isset($routeParams[$name])) {
                $params[$name] = $routeParams[$name];
            }
        }
        return $params;
    }

}