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
class EntityClass extends Entity implements EntityInterface
{

    private $reflection;

    /**
     * {@inheritdoc}
     */
    public function _append(array $defs=null)
    {
        if(isset($defs['controller'])) {
          // assume class based
          $this->controller = $defs['controller'];

        } else if(isset($defs['redirect'])) {
          //echo 'redirect';
          $this->redirect = $defs['redirect'];
        }
        // } else {
        //   # TODO  throw(new \RunTimeException('ddd'))
        //   echo '<pre>ERROR';
        //   print_r ($defs);
        // }

        //$this->controller = $defs['controller'];
    }

    /**
     * {@inheritdoc}
     */
    public function _call(Router $route)
    {
        $name = $this->controller['name'];
        $args = $this->controller['args'];

        // if(!isset($entity->controller['name'])) {
        //     $entity->controller['name'] = $route->controller_name;
        // }

        // just created ta deependency here!!!!!
        if(!isset($args)) {
            $args = $route->controller_args;
        }

        $method = $this->getMethod($route);

        $params = $this->getRequiredParams($route->getMethod(), $method, $route->getParams());

        // attach late listeners @ post-processing
        #$this->addAllListeners('resource', 'early');

        return call_user_func_array(
          array(
            new $name($args),
            $route->getAction()),
            $params
          );
    }

    /**
     * {@inheritdoc}
     */
    public function _parseDocs()
    {
        $this->reflection = new \ReflectionClass(
            $this->getController('name')
        );

        // class doc
        $this->docs = Reflection::parsePhpDoc(
            $this->reflection->getDocComment()
        );

        $actions = $this->getActions();

        // doc for all methods
        foreach($this->getMethods() as $key => $method)
        {
          if( $key = array_search($method->name, $actions) ) {
            $doc = $method->getDocComment();
            $this->docs['methods'][$key] =
                Reflection::parsePhpDoc( $doc );
          }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(Router $route)
    {
        $name = $route->getAction();
        if($this->reflection->hasMethod($name)) {
            return $this->reflection->getMethod($name);
        }

        throw new \InvalidArgumentException("Invalid resource's method ({$route->getMethod()}) specified.", 405);
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $funcs = array();
        foreach ($this->getMethods() as $ref) {
            $funcs[] = $ref->name;
        }
        $routes = $this->route->getActions();

        return array_intersect($routes, $funcs);

        $all= array_intersect($routes, $funcs);
        return $all+$this->overrides;
    }

    private function getMethods()
    {
        return $this->reflection->getMethods(
            \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC
        );
    }

}