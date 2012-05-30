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
    public function __construct(Server $server)
    {
        $this->server = $server;

        // attach late listeners @ post-processing
      #  $this->addAllListeners('resource', 'early');
    }

    /**
     * Return the classname for a resource (long and private)
     *
     * @params	string	$name
     * @return string
     * @throws Zenya\Api\Exception If it doesn't not exist.
     */
    public function getInternalAppelation(Router $route)
    {
        switch ($route->method) {
            case 'OPTIONS':	// help about a resource
            case 'HEAD':    // test a resource
                $route->params = array(
                        'resource'  => $route->name,
                        'params'    => $route->params
                    );
                $route->name = 'HTTP_' . $route->method;
            break;
        }
        return $this->server->getResource($route->name);
    }

    /**
     * Call a resource
     *
     * @params string	$name	Name of the resource
     * @return array
     * @throws Zenya\Api\Exception
     */
    public function call()
    {
        $route = $this->server->route;

        // Relection
        $classArray = self::getInternalAppelation($route);
        $className = $classArray['class'];
        $classArgs = isset($classArray['classArgs'])
            ? $classArray['classArgs']
            : $route->classArgs;

            // map to an action
            $action = $route->getAction();

        try{
            $refClass = new \ReflectionClass($className);
            $this->actions = $refClass->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC);

            $refMethod = $refClass->getMethod($action);

            // check the actionMethod
            if (
                !in_array($refMethod, $this->actions)
                && !$refMethod->isConstructor()
                && !$refMethod->isAbstract()
            ) {
                throw new Exception();
            }
        } catch(\Exception $e) {
            throw new Exception("Invalid resource's method ({$route->method}) specified.", 405);
        }

        // check the Params
        $params = array();
        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            if (
                !$param->isOptional()
                && !array_key_exists($name, $route->params)
                && empty($route->params[$name])
            ) {
                throw new Exception("Required {$route->method} parameter \"{$name}\" missing in action.", 400);
            } elseif (isset($route->params[$name])) {
                $params[$name] = $route->params[$name];
            }
        }

        // TODO: maybe we need to check the order of params key match the method?

        // TODO: type casting handler

        // attach late listeners @ post-processing
        $this->addAllListeners('resource', 'early');

        return call_user_func_array(array(new $className($classArgs), $action), $params);
    }

    public function getMethods()
    {
        $actions = array();
        foreach($this->actions as $obj) {
                $actions[] = $obj->name;
        }
        $methods = array_intersect($this->server->route->getActions(), $actions);
        return array_keys($methods);
    }

}