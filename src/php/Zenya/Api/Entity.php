<?php

namespace Zenya\Api;

use Zenya\Api\Listener;
use Zenya\Api\Router;
use Zenya\Api\Entity\EntityInterface;

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

  public $group = '/* -- todo group */';

  protected $overrides = array('OPTIONS'=>'help', 'HEAD'=>'test');

  #protected $route=null;

  // protected $doc;
  // protected $action;
  // protected $method;
  // protected $methods = array();

    private $_ref = null;

    public function debug($data=null)
    {
          echo '<pre>';
          print_r($data!==null?$data:$this);
    }

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
     * @param  string $name     The resource name
     * @param  array  $resource The resource definition array
     * @return void
     */
    public function group($name)
    {
        // group test
        $this->group = $name; //'/* TODO: string from group!! */';

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
     * Check for a redirect.
     *
     * @param  array  $resource The resource definition array
     * @return void
     */
    // public function hasRedirect()
    // {
    //     return isset($this->redirect);
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

    public function toArray()
    {
      return get_object_vars($this);
    }

    /**
     * Call a resource from route
     *
     * @params Router   $route  Route object
     * @return array
     * @throws Zenya\Api\Exception
     */
    public function call(Router $route)
    {
      $this->route = $route;
      
      try {
        
        // cache?!
        $this->_parseDocs();
        
        //$this->actions = $this->getActions();

      } catch (\Exception $e) {
        throw new \RuntimeException("Resource entity without implementation.", 500);
      }

      if($route->getMethod() == 'OPTIONS') {
          return $this->getDocs();
      }

      return $this->_call($route);
   }

    public function getController($key=null)
    {
      if(null !== $key) {
        return isset($this->controller[$key]) ? $this->controller[$key] : null;
      }
      return $this->controller;
    }

    public function getAction($method)
    {
      return $this->actions[$method]['action'];
    }

    public function getCurrentAction()
    {
      $method = $this->route->getMethod();
      return $this->getAction($method);
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
     * Returns the full Documentatins or specified method. 
     *
     * @param  string  $method
     * @return array
     */
    public function getDocs($method=null)
    {
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