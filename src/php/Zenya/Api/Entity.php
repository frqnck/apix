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

    protected $overrides = array('OPTIONS'=>'help', 'HEAD'=>'test');

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
     * Call the resource entity applying any method overrides.
     *
     * @return array
     * @throws Zenya\Api\Exception
     * @see EntityInterface::_call
     *
     * @todo OPTIONS returns the help array for now, will need to redirect the entity instead... using clone!
     */
    public function call()
    {
        // TODO: this is temporary...
        if ($this->route->getMethod() == 'OPTIONS') {
            return $this->getDocs();
        }

        /*
            $this->route->setParams(
              array('entity' => clone $this)
            );

            // TODO: review this
            $c = Config::getInstance();
            $alt = $c->getResources($this->overrides[$method]);
            // TODO: auto inject here!!
            $alt['controller']['args'] = $c->getInjected('Server');

            $this->controller = $alt['controller'];

            #$this->ref = new Reflection( $this->parseDocs() );
        */

        return $this->underlineCall($this->route);
   }

    /**
     * To array...
     *
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
                throw new \BadMethodCallException("Required {$httpMethod} parameter \"{$name}\" missing in action.", 400);
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
