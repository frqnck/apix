<?php

namespace Zenya\Api;

use Zenya\Api\Listener,
    Zenya\Api\Router,
    Zenya\Api\Entity\EntityInterface;

/**
 * Represents a resource.
 *
 */
class Entity extends Listener
{
    protected $docs;

    protected $route;

    protected $redirect;

    protected $defaultActions = array('OPTIONS'=>'help', 'HEAD'=>'test');

    /**
     * Appends the given array definition and apply generic mappings.
     *
     * @param  array $definitions
     * @return void
     * @see EntityInterface::_append
     */
    final public function _append(array $defs)
    {
        if (isset($defs['redirect'])) {
            $this->redirect = $defs['redirect'];
        }
    }

    /**
     * Call the resource entity.
     *
     * @return array
     * @throws Zenya\Api\Exception
     * @see EntityInterface::_call
     *
     * @todo OPTIONS returns the help array for now, will need to redirect the entity instead... using clone!
     */
    public function call()
    {
        //$method = $this->route->getMethod();

        // if ($method == 'OPTIONS') {
        //     return $this->getDocs();
        // }

        #print_r( $this );
        #print_r($this->getActions());

        // Handler for the generic methods.
        // if( !$this->hasMethod($method)
        //     && $this->hasMethod($method, $this->overrides)
        // ) {
        //     $this->route->setParams(
        //         array('entity' => clone $this)
        //     );

        //     // TEMP: should be a DIC here!
        //     $c = Config::getInstance();
        //     $alt = $c->getResources($this->overrides[$method]);

        //     // TEMP: and here, auto inject!
        //     $alt['controller']['args'] = $c->getInjected('Server');

        //     $this->controller = $alt['controller'];

        //     print_r( $this->controller['name'] );


        //     // $this->controller = array(
        //     //    'name' => 'Zenya\Api\Resource\Help',
        //     //    'args' => $this->route->controller_args
        //     // );
        // }

        return $this->underlineCall($this->route);
   }

    /**
     * Does this entity hold the specified method?
     *
     * @param   string    $method
     * @param   array     $actions=null   Use to override local actions.
     * @return  boolean
     */
    public function hasMethod($method, array $actions = null)
    {
        $actions = null === $actions ? $this->getActions() : $actions;
        return in_array($method, array_keys($actions));
    }

    /**
     * Returns all the default actions.
     *
     * @return  array
     */
    public function getDefaultActions()
    {
        return $this->defaultActions;
    }

    /**
     * Gets the speified default action.
     *
     * @return  string
     */
    public function getDefaultAction($method)
    {
        return $this->defaultActions[$method];
    }

    /**
     * To array...
     *
     * @return  array
     */
    public function toArray()
    {
      return get_object_vars($this);
    }

    /**
     * Returns the full or just the specified method documentation .
     *
     * @param  string $method
     * @return array
     */
    public function getDocs($method=null)
    {
        if (null === $this->docs) {
            $this->docs = $this->_parseDocs();
        }

        if (null !== $method) {
            return isset($this->docs['methods'][$method])
                    ? $this->docs['methods'][$method] : null;
        }

        return $this->docs;
    }

    /**
     * Returns the validated required parameters.
     *
     * @param  \ReflectionFunctionAbstract $refMethod   A reflected method/function to introspect.
     * @param  string                      $httpMethod  A public method name e.g. GET, POST.
     * @param  array                       $routeParams An array of route parameters to check upon.
     * @return array                       The array of required parameters
     * @throws \BadMethodCallException     400
     */
    public function getRequiredParams(\ReflectionFunctionAbstract $refMethod, $httpMethod, array $routeParams)
    {
        $params = array();
        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            if (
                !$param->isOptional()
                && !array_key_exists($name, $routeParams)
            ) {

                // auto inject local classes
                if($class = $param->getClass()) {
                    $obj = strtolower(str_replace(__NAMESPACE__ . '\\', '', $class->getName()));
                    $params[$name] = $obj == 'server' ? $this->route->server : $this->route->server->$obj;
                } else {
                    throw new \BadMethodCallException("Required {$httpMethod} parameter \"{$name}\" missing in action.", 400);
                }

            } elseif (isset($routeParams[$name])) {
                $params[$name] = $routeParams[$name];
            }
        }
        // TODO: maybe we need to check the order of params key match the method?
        // TODO: maybe add a type casting handler here
        return $params;
    }

    /**
     * Sets the route object.
     *
     * @param  Router $route
     * @return void
     */
    public function setRoute(Router $route)
    {
        $this->route = $route;
    }

    /**
     * Returns the route object.
     *
     * @return Router
     */
    // public function getRoute()
    // {
    //     return $this->route;
    // }


    /**
     * Returns the redirect location.
     *
     * @return string
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Check wether is public or not.
     *
     * @return boolean
     */
    public function isPublic()
    {
        $method = $this->route->getMethod();

        $doc = $this->getDocs($method);

        $role = isset($doc['api_role'])
          ? $doc['api_role']
          : false;

        if (!$role || $role == 'public') {
          return true;
        }

        return false;
    }

}
