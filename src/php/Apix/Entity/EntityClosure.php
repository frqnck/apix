<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix\Entity;

use Apix\Entity,
    Apix\Entity\EntityInterface,
    Apix\Reflection,
    Apix\Router;

/**
 * Represents a resource.
 *
 */
class EntityClosure extends Entity implements EntityInterface
{

    /**
     * Holds group info...
     */
    protected $group = null;
    // protected $group = array(
    //         // 'title' => 'some group title...',
    //         // 'description' => 'some group description..',
    //         // 'bb' => 'ss'
    //     );

    private $reflection;

    /**
     * Sets and returns the reflection of a function.
     *
     * @param  string                    $name The REST name of function.
     * @return \ReflectionFunction|false
     */
    public function reflectedFunc($name)
    {
        if (isset($this->reflection[$name])) {
            return $this->reflection[$name];
        } elseif ( isset($this->actions[$name]['action'])
            && $this->actions[$name]['action'] instanceof \Closure
        ) {
            $this->reflection[$name] = new \ReflectionFunction($this->actions[$name]['action']);

            return $this->reflection[$name];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function append(array $def)
    {
        parent::_append($def);
        // if (!isset($def['method'])) {
        //     throw new \RuntimeException('Closure not defining a method, somehting must be wrong!?');
        // }
        $this->actions[$def['method']] = $def;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function setActions(array $asso = null)
    {
        // obsolete!
    }

    /**
     * {@inheritdoc}
     */
    public function underlineCall(Router $route)
    {
        $method = $this->getMethod($route);

        #try {
            $action = $this->getAction($route->getMethod());
        #} catch (\Exception $e) {
        #    throw new \RuntimeException("Resource entity not (yet) implemented.", 501);
        #}

        $params = $this->getValidatedParams($method, $route->getMethod(), $route->getParams());

        return call_user_func_array($action, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function parseDocs()
    {
        // group class doc
        // $docs = Reflection::parsePhpDoc( $this->group );
        $docs = $this->group;

        // doc for all methods
        foreach ($this->getActions() as $key => $func) {
            $ref = $this->reflectedFunc($key);
            $docs['methods'][$key] = Reflection::parsePhpDoc($ref); // <- TODO (required args)

            // HERE: temp
            $docs['methods'][$key]['method'] = $key;

            $docs['methods'][$key]['path'] = $key . ' '; //. $this->getAction($key); // tood here 

        }

        return $docs;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod(Router $route)
    {
        $name = $route->getMethod();
        if (false === $method = $this->reflectedFunc($name)) {
            throw new \InvalidArgumentException(
                "Invalid resource's method ({$name}) specified.",
                405
            );
        }

        return $method;
    }

    private function getAction($method)
    {
      return $this->actions[$method]['action'];
    }

    /* --- CLOSURE only --- */

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

    /**
     * Group a resource entity.
     *
     * @param  string $name The group name
     * @return void
     */
    public function group($title)
    {
        // var_dump('dd');exit;
        // public $group = null;
        // $this->group = ['title'=> 'd', 'documentation'=>'some'];

        // TODO retrive phpdoc coment strinfg here!

        // group test
        $this->group = ['title'=>$title];

        return $this;
    }

}