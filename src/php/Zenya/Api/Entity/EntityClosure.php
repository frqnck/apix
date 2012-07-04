<?php

namespace Zenya\Api\Entity;

use Zenya\Api\Entity,
    Zenya\Api\Entity\EntityInterface,
    Zenya\Api\Reflection,
    Zenya\Api\Router;

/**
 * Represents a resource.
 *
 */
class EntityClosure extends Entity implements EntityInterface
{

    protected $actions = array();

    private $reflection;

    public $group;

    public function getReflection($name)
    {

        if (null == $this->reflection[$name]) {
          if (isset($this->actions[$name]['action']) && $this->actions[$name]['action'] InstanceOf \Closure) {
            $this->reflection[$name] = new \ReflectionFunction($this->actions[$name]['action']);
          } else {
            return false;
          }
        }

        return $this->reflection[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function append(array $defs)
    {
        parent::_append($defs);
        $this->actions[$defs['method']] = $defs;
    }

    /**
     * {@inheritdoc}
     */
     function underlineCall(Router $route)
    {
      #if (!isset($this->actions[$route->getMethod()])) {
#          throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified.!!", 405);
#      }

      #try {
            $method = $this->getMethod($route);
            $action = $this->getAction($route->getMethod());

       # } catch (\Exception $e) {
       #   throw new \RuntimeException("Resource entity not implemented.");
      #  }

        // TODO: merge with TEST & OPTIONS ???

        $params = $this->getRequiredParams($method, $route->getMethod(), $route->getParams());

        #$this->addAllListeners('resource', 'early');

        return call_user_func_array($action, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function _parseDocs()
    {
        // class doc
        $docs = Reflection::parsePhpDoc( $this->group );

        // doc for all methods
        foreach ($this->getActions() as $key => $func) {
          if ($func['action'] InstanceOf \Closure) {
              #$r = $this->getReflection($key);
              $this->reflection[$key] = new \ReflectionFunction($func['action']);
              $doc = $this->reflection[$key]->getDocComment();
              $docs['methods'][$key] = Reflection::parsePhpDoc( $doc );
          }
        }

        return $docs;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(Router $route)
    {
        $name = $route->getMethod();
        $r = $this->getReflection($name);
        if (false !== $r) {
          return $r;
        }

        throw new \InvalidArgumentException("Invalid resource's method ({$name}) specified.", 405);
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions+$this->overrides;
        return $this->actions;
    }

    private function getAction($method)
    {
      return $this->actions[$method]['action'];
    }

    /* --- CLOSURE only --- */

    /**
     * Group a resource entity.
     *
     * @param  string $name The group name
     * @return void
     */
    public function group($test)
    {
        // TODO retrive phpdoc coment strinfg here!
        #$test = "/* TODO {closure-group-title} */";
        // group test
        $this->group = $test;

        return $this;
    }

    /**
     * Adds a redirect.
     *
     * @param  string $location A  name
     * @param  array  $resource The resource definition array
     * @return void
     */
    public function redirect($location)
    {
        $this->redirect = $location;

        return $this;
    }
}
