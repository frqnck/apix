<?php

namespace Zenya\Api;

use Zenya\Api\Listener,
    Zenya\Api\Router,
    Zenya\Api\Entity\EntityInterface;

/**
 * Represents a resource.
 *
 */
class Entity extends Listener #implements EntityInterface
{
    protected $name;
    protected $controller;
    protected $actions = array();
    protected $redirect = null;
    protected $docs = null;

    protected $route = null;

    public $group = '/* -- todo group -- */';

    protected $overrides = array('OPTIONS'=>'help', 'HEAD'=>'test');

    /**
     * {@inheritdoc}
     */
    public function append(array $defs=null)
    {
        $this->_append($defs);
    }

    /**
     * Group a resource entity.
     *
     * @param  string $name     The group name
     * @return void
     */
    public function group($name)
    {
        // group test
        $this->group = $name;

        return $this;
    }

    /**
     * Adds a redirect.
     *
     * @param  string $location   A  name
     * @param  array  $resource The resource definition array
     * @return void
     */
    public function redirect($location)
    {
        $this->redirect = $location;

        return $this;
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
     * To array...
     *
     */
    public function toArray()
    {
      return get_object_vars($this);
    }

    /**
     * Sets router object.
     *
     * @param Router $route 
     * @return void
     */
    public function setRoute(Router $route)
    {
        $this->route = $route;
    }

    /**
     * Call the resource entity from route
     *
     * @return array
     * @throws Zenya\Api\Exception
     */
    public function call()
    {
        try {

            // cache?!
            $this->_parseDocs();

            //$this->actions = $this->getActions();

        } catch (\Exception $e) {
            throw new \RuntimeException("Call to a unimplemented resource entity.", 500);
        }

        // return the help...
        if($this->route->getMethod() == 'OPTIONS') {
          //return $this->getDocs();
        }

        // attach the early listeners @ pre-processing stage
        #$this->addAllListeners('entity', 'early');

        return $this->_call($this->route);
   }

    public function getController($key=null)
    {
      if(null !== $key) {
        return isset($this->controller[$key]) ? $this->controller[$key] : null;
      }

print_r($this->controller);
exit;

      return $this->controller;
    }

    public function isPublic()
    {
        // $verb = isset($this->_ref)
        //   ? $this->route->getAction()   // closure
        //   : $this->route->getMethod();

        $method = $this->route->getMethod();

        $doc = $this->getDocs($method);

        $role = isset($doc['api_role'])
          ? $doc['api_role']
          : false;

        if( !$role || $role == 'public') {
          return true;
        }

        return false;
    }

    public function getRequiredParams($httpMethod, $refMethod, array $routeParams)
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
     * Returns the full Documentations or specified method.
     *
     * @param  string  $method
     * @return array
     */
    public function getDocs($method=null)
    {
        if(null === $this->docs) {
            $this->_parseDocs();
        }

        if(null !== $method) {
            return isset($this->docs['methods'][$method]) ? $this->docs['methods'][$method] : 'false';
        }

        return $this->docs;

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
    }

}