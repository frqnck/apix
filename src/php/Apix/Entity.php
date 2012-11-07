<?php

namespace Apix;

use Apix\Listener,
    Apix\Router,
    Apix\Entity\EntityInterface;

/**
 * Represents a resource.
 *
 */
class Entity extends Listener
{
    protected $docs;
    protected $route;
    protected $redirect;
    protected $actions = null;
    protected $defaultActions = array(
        'OPTIONS' => 'help',
        'HEAD' => 'test'
    );

    /**
     * Holds the output of the resource entity.
     */
    protected $output = null;

    /**
     * Appends the given array definition and apply generic mappings.
     *
     * @param   array $def An entity array definition.
     * @return  void
     * @see     EntityInterface::_append
     */
    final public function _append(array $def)
    {
        if (isset($def['redirect'])) {
            $this->redirect = $def['redirect'];
        }
    }

    /**
     * Call the resource entity.
     *
     * @return  array
     * @throws  Apix\Exception
     * @see     EntityInterface::_call
     */
    public function call()
    {
        if(null === $this->output) {
            $this->output = $this->underlineCall($this->route);
        }

        return $this->output;
    }

    /**
     * Checks wether the current entity holds the specified method.
     *
     * @param  string  $method
     * @param  array   $actions=null Use to override local actions.
     * @return boolean
     */
    public function hasMethod($method)
    {
        return array_key_exists($method, $this->getActions());
    }

    /**
     * Returns all the available actions.
     *
     * @return array
     */
    public function getAllActions()
    {
        $current = null === $this->getActions() ? array() : $this->getActions();
        $default = $this->defaultActions;
        if (false == array_key_exists('GET', $current) ) {
            unset($default['HEAD']);
        }

        return $current+$default;
    }

    /**
     * Gets the specified default action.
     *
     * @return string
     */
    public function getDefaultAction($method)
    {
        if (isset($this->defaultActions[$method])) {
            return $this->defaultActions[$method];
        }
    }

    /**
     * To array...
     *
     * @return array
     */
    public function toArray()
    {
      return get_object_vars($this);
    }

    /**
     * Returns the full class/group or specified method documentation .
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
     * Returns the validated and required parameters.
     *
     * @param  \ReflectionFunctionAbstract $refMethod   A reflected method/function to introspect.
     * @param  string                      $httpMethod  A public method name e.g. GET, POST.
     * @param  array                       $routeParams An array of route parameters to check upon.
     * @return array                       The array of validated and required parameters
     * @throws \BadMethodCallException     400
     */
    public function getValidatedParams(\ReflectionFunctionAbstract $refMethod, $httpMethod, array $routeParams)
    {
        $params = array();
        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            if (
                !$param->isOptional()
                && !array_key_exists($name, $routeParams)
            ) {

                // auto inject local objects
                if ($class = $param->getClass()) {
                    $obj = strtolower(str_replace(__NAMESPACE__ . '\\', '', $class->getName()));
                    $params[$name] = $obj == 'server' ? $this->route->server : $this->route->server->$obj;
                } else {
                    throw new \BadMethodCallException("Required {$httpMethod} parameter \"{$name}\" missing in action.", 400);
                }

            } elseif (isset($routeParams[$name])) {
                $params[$name] = $routeParams[$name];
            }
        }
        // TODO: maybe we need to check the order of params to match the method?
        // TODO: eventually add type casting using namespacing e.g. method(integer $myInteger) => Apix\Casting\Integer, etc...
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
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Returns the redirect location.
     *
     * @return string
     */
    public function hasRedirect()
    {
        return isset($this->redirect);
    }

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
     * Returns an array of method keys and action values.
     *
     * @param  array $array
     * @return array
     */
    public function getActions()
    {
        if (null === $this->actions) {
            $this->setActions();
        }

        return $this->actions;
    }

    /**
     * Returns the value of an anotation.
     *
     * @param  string   $name
     * @return mix|null
     */
    public function getAnnotationValue($name)
    {
        $method = $this->route->getMethod();
        $doc = $this->getDocs($method);

        return isset($doc[$name])
          ? $doc[$name]
          : null;
    }

    /* --- ANYTHING BELOW TO BE MOVED ELSEWHERE (plugins) --- */

    /**
     * Check wether is public or not.
     *
     * @return boolean
     */
    public function OFFisPublic()
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
