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

    private $reflection;

    /**
     * {@inheritdoc}
     */
    public function _append(array $defs)
    {
      $this->actions[$defs['method']] = $defs;
    }

    /**
     * {@inheritdoc}
     */
     function _call(Router $route)
    {
      #if(!isset($this->actions[$route->getMethod()])) {
#          throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified.!!", 405);
#      }

      #try {
            $method = $this->getMethod($route);
            $action = $this->getAction($route->getMethod());

       # } catch (\Exception $e) {
       #   throw new \RuntimeException("Resource entity not implemented.");
      #  }

        // TODO: merge with TEST & OPTIONS ???

        $params = $this->getRequiredParams($route->getMethod(), $method, $route->getParams());

        #$this->addAllListeners('resource', 'early');

        return call_user_func_array($action, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function _parseDocs()
    {
        // class doc
        $this->docs = Reflection::parsePhpDoc(
            $this->group
        );

      // doc for all methods
      foreach($this->getActions() as $key => $func) {
          if($func['action'] InstanceOf \Closure) {
              $this->reflection[$key] = new \ReflectionFunction($func['action']);
              $doc = $this->reflection[$key]->getDocComment();
              $this->docs['methods'][$key] = Reflection::parsePhpDoc( $doc );
          }
      }
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(Router $route)
    {
        $name = $route->getMethod();
        if(isset($this->reflection[$name])) {
          return $this->reflection[$name];
        }

        throw new \InvalidArgumentException("Invalid resource's method ({$name}) specified.!!", 405);
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

    // private function getCurrentAction()
    // {
    //   $method = $this->route->getMethod();
    //   return $this->getAction($method);
    // }

}