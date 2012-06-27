<?php

namespace Zenya\Api;

/**
 * Represents a resource.
 *
 */
class Entity extends Listener
{

  protected $name;
  protected $controller;
  protected $actions = array();
  protected $alias;

  protected $overrides = array('OPTIONS'=>'help', 'HEAD'=>'test');

  #protected $route=null;

  // protected $doc;
  // protected $action;
  // protected $method;
  // protected $methods = array();

    public function debug($data=null)
    {
          echo '<pre>';
          print_r($data!==null?$data:$this);
    }

    /**
     * Import given objects
     *
     * @param array $resources
     */
    public function __construct(array $group=null)
    {
        #$this->route = $route;
        // attach late listeners @ post-processing
        #$this->addAllListeners('resource', 'early');

        // group test
        $this->group = $group; //'/* TODO: string from group!! */';
    }

    /**
     * Adds a resource entity.
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

    public function append(array $defs=null)
    {

      if( isset($defs['action'])
          && $defs['action'] instanceOf \Closure
        ) {
          $this->isClosure = true;

          $this->actions[$defs['method']] = $defs;

        } else if(isset($defs['controller'])) {
          $this->isClosure = false;

          // assume class based
          $this->controller = $defs['controller'];
        } else if(isset($defs['alias'])) {
          $this->alias = $defs['alias'];
        } else {
          echo '<pre>ERROR';
          $this->debug($defs);
        }

        #$this->route = $route;

        // attach late listeners @ post-processing
        #$this->addAllListeners('resource', 'early');
    }


    public function isClosure($action=null)
    {
      if($action === null)
        return $this->isClosure;
      else
        return $action instanceOf \Closure;
    }

    public function toArray()
    {
      return get_object_vars($this);
    }

    /**
     * Returns all the actions available.
     *
     * @param  Router $route
     * @return string
     */
    public function getActions()
    {
        return $this->actions+$this->overrides;
    }

    /**
     * Return the classname for a resource (long and private)
     *
     * @param  Router $route
     * @return string
     */
    public function doOverrides($method)
    {
        if(
            !isset($this->actions[$method])
            && in_array($method, array_keys($this->overrides))
        ) {

          $this->route->setParams(
            array('entity' => clone $this)
          );

          // TODO: review this
          $c = Config::getInstance();
          $alt = $c->getResources($this->overrides[$method]);
          // TODO: auto inject here!!
          $alt['controller']['args'] = $c->getInjected('Server');

          $this->controller = $alt['controller'];
          $this->isClosure = false;

          $this->ref = new Reflection($this);
        }
    }

    /**
     * Returns an array of method => action/func
     *
     * @param  array $array
     * @return array
     */
    public function getActionsMethods(array $routes=array())
    {
        if(!$this->isClosure()) {
            $refs = $this->ref->getMethods();
            $funcs = array();
            foreach ($refs as $ref) {
                $funcs[] = $ref->name;
            }

            $routes = $this->route->getActions();

            return array_intersect($routes, $funcs);
        } else {
            return $this->actions;
        }
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
        $this->ref = new Reflection($this);
        $this->actions = $this->getActionsMethods();
      } catch (\Exception $e) {
        echo $e->getMessage();
        throw new \RuntimeException("Resource entity has no implementation.", 500);
      }

      // check for a method override;
      $this->doOverrides( $route->getMethod() );

      // Delegate calls to a controller
      if(!$this->isClosure()) {
        return $this->_class($route);
      } else {
        // assume closure based
        return $this->_closure($route);
      }

      throw new \RuntimeException("Resource entity missing an implementation.");
    }

    protected function _class($route)
    {
        $name = $this->controller['name'];
        $args = $this->controller['args'];

        try {
          $method = $this->ref->getMethod( $route->getAction() );
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified (class).", 405);
        }

        $params = $this->getRequiredParams($route->getMethod(), $method, $route->getParams());

        // attach late listeners @ post-processing

        $this->addAllListeners('resource', 'early');

        return call_user_func_array(
          array(
            new $name($args),
            $route->getAction()),
            $params
          );
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

    protected function _closure($route)
    {
      if(!isset($this->actions[$route->getMethod()])) {
          throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified.", 405);
      }

      try {
          $action = $this->getAction($route->getMethod());

          $method = new ReflectionFunc($action);
        } catch (\Exception $e) {
          throw new \RuntimeException("Resource entity not implemented.");
        }

        // TODO: merge with TEST & OPTIONS ???

        $params = $this->getRequiredParams($route->getMethod(), $method, $route->getParams());
        $this->addAllListeners('resource', 'early');

        return call_user_func_array($action, $params);
    }

    public function isPublic()
    {
        #$verb = isset($this->ref)
        #  ? $this->route->getAction()   // closure
        #  : $this->route->getMethod();

        $verb = $this->route->getMethod();

        $docs = $this->ref->getDocs( $verb );

       # echo '<pre>'; print_r($docs['methods']);#exit;
        #$docs = $this->getDocs($verb);

        $role = isset($docs['methods'][$verb]['api_role'])
          ? $docs['methods'][$verb]['api_role']
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

}