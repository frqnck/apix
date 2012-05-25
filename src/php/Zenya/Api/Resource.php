<?php

namespace Zenya\Api;

class Resource extends Listener
{

    /**
     * Stores the resources'objects.
     *
     * @var	array
     */
    protected $resources = array();

    /**
     * Import given objects
     *
     * @param array $resources
     */
    public function __construct(array $resources)
    {
        $internals = array(
            'HTTP_OPTIONS' => array(
                    'class'		=>	'Zenya\Api\Resource\Help',
                    #'classArgs'	=> array('resource' => &$this),
                    'args'		=> array('params'=>'dd')
                ),
            'HTTP_HEAD' => array(
                    'class'		=> 'Zenya\Api\Resource\Test',
                    'classArgs'	=> array('resource' => &$this),
                    'args'		=> array()
                )
        );

        $this->resources = $resources+$internals;
    }

    /**
     * Get the full resources array
     *
     * @return array array of resources.
     */
    public function getResources()
    {
        return $this->resources;
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
            case 'OPTIONS':	// help
                $route->name = 'HTTP_OPTIONS';
                break;
            case 'HEAD':	// test
                $route->name = 'HTTP_HEAD';
                break;
        }

        if (!array_key_exists($route->name, $this->resources)) {
            throw new Exception("Invalid resource's name specified ({$route->name})", 404);
        }

        return $this->resources[$route->name];
    }

    /**
     * Act as a data mapper, check required params, etc...
     *
     * @return void
     * @throws Zenya_Api_Exception
     */
    private function checkRequirments($method, array $params)
    {
        foreach ($this->_requirements as $key => $value) {
            if (in_array($method, $value)) {
                if (true === is_int($key)) {
                    continue;
                }
                if (!array_key_exists($key, $params) || empty($params[$key])) {
                    throw new Exception("Required {$method} parameter \"{$key}\" missing in action.", 400);
                }
            }
        }
    }

    /**
     * Call a resource
     *
     * @params string	$name	Name of the resource
     * @return array
     * @throws Zenya\Api\Exception
     */
    public function call(Server $server)
    {
        $this->server = $server;

        // attach late listeners @ post-processing
        $this->addAllListeners('resource', 'early');

        $route = $this->server->route;

        // Relection
        $class		= self::getInternalAppelation($route);
        $className	= $class['class'];
        $classArgs = isset($class['classArgs'])
            ? $class['classArgs']
            : $route->classArgs;

        $refClass = new \ReflectionClass($className);

        // Array of HTTP Methods to CRUD verbs.
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

        return call_user_func_array(array(new $className($classArgs), $route->action), $params);
    }

}
