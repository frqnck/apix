<?php

namespace Apix;

/**
 * Represents a resource.
 *
 */
class Resource extends Listener
{

  protected $name;
  protected $controller;

  protected $actions=array();

  protected $route=null;

  // protected $doc;

  // protected $action;
  // protected $method;

  // protected $commits = array();
  // protected $building = false;
  // protected $notifiers = array();
  #protected $methods = array();

    private $refClass, $refMethod;

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
     * Adds Method Overrides
     *
     * @param  Router $route
     * @return string
     */
    public function getActions()
    {
        $overrides = array('OPTIONS'=>'help', 'HEAD'=>'test');

        return $this->actions+$overrides;
    }

    /**
     * Return the classname for a resource (long and private)
     *
     * @param  Router $route
     * @return string
     */
    public function setRouteOverrides()
    {
        $overrides = array('OPTIONS', 'HEAD');
        $method = $this->route->getMethod();

        // use override if not set.
        if(
          !isset($this->resource['actions'][$method])
          && in_array($method, $overrides)
        ) {

          $c = Config::getInstance();
          $res = $c->getResources($this->route->getAction());
          $res['controller']['args'] = $c->getInjected('Server');

          // set as action alias (TEMP)
          $this->resource['actions'][$method]['alias'] = $res;

          $this->route->setParams(
              array(
                'resource'     => 'todo-path-to-res?', //todo path? $route->getController(),
                'http_method'  => $this->route->hasParam('http_method')
                                  ? $tis->route->getParam('http_method')
                                  : null,
              )
          );
        }
    }

    /**
     * Call a resource from route
     *
     * @params Router	$route	Route object
     * @return array
     * @throws Apix\Exception
     */
    public function call($entity)
    {
      $this->resource = $entity;

      // TODO: add to actions!!! if not implemented.
      // use actions as the standad mean (Refactor).
      $this->setRouteOverrides();

      // Alias: current action has an aliased controller
      $action = isset($this->resource['actions'][$this->route->getMethod()]['alias'])
            ? $this->resource['actions'][$this->route->getMethod()]['alias']
            : null;

      if (isset($action['controller'])) {
          $this->resource['controller'] = $action['controller'];

          #$this->resource['controller'][]
      }

      // Delegate calls to a controller
      if(isset($this->resource['controller']['name'])
          #&& is_callable( addslashes($resource['controller']['name']) )
      ) {
        return $this->_class($this->resource, $this->route);
      }

      // assume closure based
      #if ($action instanceOf \Closure) { //is_callable( $action )) {
      return $this->_closure($this->resource, $this->route);
      #}

      throw new \RuntimeException("Resource entity missing an implementation.");
    }

    protected function _class($resource, $route)
    {
        $name = $resource['controller']['name'];
        $args = $resource['controller']['args']; //) ? $resource['controller']['args'] :null;

        // inject server object in the constructor!

//           $c = Config::getInstance();
//           $res = $c->getResources($this->route->getAction());
//           $res['controller']['args'] = $c->getInjected('Server');
//           $this->resource['actions'][$method]['alias'] = $res;
// print_r($args);

        try {
          $this->refClass = new ReflectionClass($name);
          $this->actions = $this->refClass->getActionsMethods($route->getActions());
        } catch (\Exception $e) {
          throw new \RuntimeException("Resource entity not yet implemented (class)");
        }

        // TODO: merge with TEST & OPTIONS ???

        // if( !in_array($route->getMethod(), array('OPTIONS')) )
        // {

            try {
                $this->refMethod = $this->refClass->getMethod($route->getAction());
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified (class).", 405);
            }

            $params = $this->getRequiredParams($route->getMethod(), $this->refMethod, $route->getParams());

        // } else {
        //     $refMethod = $refClass->getMethod($route->getAction());
        //     $params = array();
        // }

        // TODO: maybe we need to check the order of params key match the method?

        // TODO: maybe add a type casting handler here
        #Server::d($route);exit;

        // attach late listeners @ post-processing

        // TODO: docs
        #$classDoc = RefDoc::parsePhpDoc($refClass);
        #$methodDoc = RefDoc::parsePhpDoc($refMethod);

        $this->addAllListeners('resource', 'early');

        return call_user_func_array(
          array(
            new $name($args),
            $route->getAction()),
            $params
          );
    }

    protected function _closure($resource, $route)
    {
      $this->actions = $resource['actions'];

      if (!isset($this->actions[$route->getMethod()])) {
          throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified.", 405);
      }

      try {
          $action = $this->actions[$route->getMethod()]['action'];

          $this->refMethod = new ReflectionFunc($action);
        } catch (\Exception $e) {
          throw new \RuntimeException("Resource entity not implemented.");
        }

        // TODO: merge with TEST & OPTIONS ???
        ###Server::d( $this->actions );

        $params = $this->getRequiredParams($route->getMethod(), $this->refMethod, $route->getParams());
        $this->addAllListeners('resource', 'early');

        return call_user_func_array($action, $params);
    }

    public function getDocs($action=null)
    {
        $ref = isset($this->refClass) ? $this->refClass : $this->refMethod;
        $ref->parseClassDoc();

        if ( isset($action)) {
          $ref->parseMethodDoc($action);
        } elseif (isset($this->actions)) {
          foreach ($this->actions as $method) {
             $ref->parseMethodDoc($method);
          }
        }

        return $ref->getDocs();
    }

    public function isPublic()
    {
        $verb = isset($this->refClass) ? $this->route->getAction() : $this->route->getMethod();

        $docs = $this->getDocs($verb);

        $role = isset($docs['methods'][$verb]['api_role'])
          ? $docs['methods'][$verb]['api_role']
          : false;

        if (!$role || $role == 'public') {
          return true;
        }

        return false;
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
            } elseif (isset($routeParams[$name])) {
                $params[$name] = $routeParams[$name];
            }
        }

        return $params;
    }

}
