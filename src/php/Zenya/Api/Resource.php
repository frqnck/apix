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
    protected $methods = array(); // todo extract!

    /**
     * Import given objects
     *
     * @param array $resources
     */
    public function __construct(Server $server)
    {
        $this->server = $server;

        // attach late listeners @ post-processing
        $this->addAllListeners('resource', 'early');
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

        $refClass = new \ReflectionClass($className);

        // Array of HTTP methods to CRUD verbs.
        $crud = array(
            'POST'		=> 'create',
            'GET'		=> 'read',
            'PUT'		=> 'update',
            'DELETE'	=> 'delete',
            'OPTIONS'	=> 'help',
            'HEAD'		=> 'test',
            'TRACE'		=> 'trace'
        );

        $route->action = isset($crud[$route->method])
            ? $crud[$route->method] . 'ApiResource'
            : null;

        if (null !== $route->action) {
            $refMethod = $refClass->getMethod($route->action);
        }

        // check the Method
        if (
            null === $route->action
            OR !in_array($refMethod, $refClass->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC))
            && !$refMethod->isConstructor()
            && !$refMethod->isAbstract()
        ) {
            # TODO: List all the HTTP methods on 405
            #$this->server->response->setHeader('Allow', $this->getMethods());
            #header('Allow: ' . implode(', ', $refClass->getMethods()));

            throw new Exception("Invalid resource's method ({$route->method}) specified.", 405);
        }

        // check the Params
        $params = array();
        foreach ($refMethod->getParameters() as $key => $param) {
            if (
                !$param->isOptional()
                && !array_key_exists($param->name, $route->params)
                && empty($route->params[$param->name])
            ) {
                throw new Exception("Required {$route->method} parameter \"{$param->name}\" missing in action.", 400);
            } elseif (isset($route->params[$param->name])) {
                $params[$param->name] = $route->params[$param->name];
            }
        }
        
        // TODO: re-order the params to match the method!
        print_r($refMethod->getParameters());
        echo '<hr>';
        print_r($params);
        exit;

        return call_user_func_array(array(new $className($classArgs), $route->action), $params);
    }

}
