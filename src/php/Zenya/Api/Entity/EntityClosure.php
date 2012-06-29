<?php

namespace Zenya\Api\Entity;

use Zenya\Api\Entity;
use Zenya\Api\Entity\EntityInterface;
use Zenya\Api\Reflection;
use Zenya\Api\Router;

/**
 * Represents a resource.
 *
 */
class EntityClosure extends Entity implements EntityInterface 
{

    /**
     * {@inheritdoc}
     */
    public function _append(array $defs=null)
    {
      $this->actions[$defs['method']] = $defs;
    }

    /**
     * {@inheritdoc}
     */
     function _call(Router $route)
    {
      if(!isset($this->actions[$route->getMethod()])) {
          throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified.", 405);
      }

      try {
            $action = $this->getAction($route->getMethod());
            $refMethod = $this->getMethod( $route->getMethod() );
        } catch (\Exception $e) {
          throw new \RuntimeException("Resource entity not implemented.");
        }

        // TODO: merge with TEST & OPTIONS ???

        $params = $this->getRequiredParams($route->getMethod(), $refMethod, $route->getParams());
        $this->addAllListeners('resource', 'early');

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
              $this->_ref[$key] = new \ReflectionFunction($func['action']);
              $doc = $this->_ref[$key]->getDocComment();
              $this->docs['methods'][$key] = Reflection::parsePhpDoc( $doc );
          }
      }
      //return $this->_ref;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod($name)
    {
        if(isset($this->_ref[$name])) {
          return $this->_ref[$name];
        }

        throw new \Exception('todo');
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        return $this->actions;
    }

}