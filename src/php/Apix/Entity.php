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

namespace Apix;

use Apix\Listener,
    Apix\Config,
    Apix\Router,
    Apix\Entity\EntityInterface;

/**
 * Represents a resource entity.
 */
class Entity extends Listener
{
    /**
     * Holds this entity (parsed) documentaions.
     * @var array|null
     */
    protected $docs = null;

    /**
     * @var Route
     */
    protected $route;

    /**
     * Holds the entity redirect location.
     * @var  string|null
     */
    protected $redirect;

    /**
     * Holds all the entity available actions.
     * @var  array|null
     */
    protected $actions = null;

    /**
     * Holds all default actions.
     * @var  array
     */
    protected $defaultActions = array(
        'OPTIONS' => 'help',
        'HEAD' => 'test'
    );

    /**
     * Holds the array of results of an entity.
     * @var  array|null
     */
    protected $results = null;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param Config|null $config A config object
     */
    public function __construct(Config $config = null)
    {
        $this->config = $config ?: \Apix\Config::getInstance();
    }

    /**
     * Appends the given array definition and apply generic mappings.
     *
     * @param  array $def An entity array definition.
     * @return void
     * @see    EntityInterface::_append
     */
    final public function _append(array $def)
    {
        if (isset($def['redirect'])) {
            $this->redirect = $def['redirect'];
        }
    }

    /**
     * Call the resource entity and return its results as an array.
     *
     * @return array
     * @see     EntityInterface::underlineCall
     */
    public function call($direct=false)
    {
        // early listeners @ pre-entity
        if (!$direct) {
            $this->hook('entity', 'early');
        }

        if (null === $this->results) {
            $this->results = $this->underlineCall($this->route);
        }

        // late listeners @ post-entity
        if (!$direct) {
            $this->hook('entity', 'late');
        }

        return self::convertToArray($this->results);
    }

    /**
     * Converts the provided variable to an array.
     *
     * @param  mixed $mix
     * @return array
     */
    public static function convertToArray($mix)
    {
        switch(true):
            case is_object($mix):
                // TODO: convert nested objects recursively...
                return get_object_vars($mix);

            case is_string($mix):
                return array($mix);

            default: // so it must be an array!

                return $mix;
        endswitch;
    }

    /**
     * Checks wether the current entity holds the specified method.
     *
     * @param  string  $method
     * @param  array   $actions=null Use to override local actions.
     * @return boolean
     */
    public function hasMethod($method)
    {
        return array_key_exists($method, $this->getActions());
    }

    /**
     * Returns all the available actions.
     *
     * @return array
     */
    public function getAllActions()
    {
        $current = null === $this->getActions() ? array() : $this->getActions();
        $default = $this->defaultActions;
        if (false == array_key_exists('GET', $current) ) {
            unset($default['HEAD']);
        }

        return $current+$default;
    }

    /**
     * Gets the specified default action.
     *
     * @return string|null
     */
    public function getDefaultAction($method)
    {
        if (isset($this->defaultActions[$method])) {
            return $this->defaultActions[$method];
        }
    }

    /**
     * Returns this entity as an associative array.
     *
     * @return array
     */
    public function toArray()
    {
      return get_object_vars($this);
    }

    /**
     * Returns the full class/group or specified method documentation.
     *
     * @param  string $method
     * @return array
     */
    public function getDocs($method=null)
    {
        if (null == $this->docs) {
            $name = 'apix_docs';
            if (!$this->config->get('cache_annotation')) {
                $this->docs = $this->parseDocs();
            } elseif (false === $this->docs = apc_fetch($name)) {
                apc_store($name, $this->docs = $this->parseDocs());
            }
        }

        if (null !== $method) {
            return isset($this->docs['methods'][$method])
                    ? $this->docs['methods'][$method] : null;
        }

        return $this->docs;
    }

    /**
     * Returns the validated and required parameters.
     *
     * @param  \ReflectionFunctionAbstract $refMethod   A reflected method/function to introspect.
     * @param  string                      $httpMethod  A public method name e.g. GET, POST.
     * @param  array                       $routeParams An array of route parameters to check upon.
     * @return array                       The array of validated and required parameters
     * @throws \BadMethodCallException     400
     */
    public function getValidatedParams(
        \ReflectionFunctionAbstract $refMethod, $httpMethod, array $routeParams
    ) {
        $params = array();
        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            if (
                !$param->isOptional()
                && !array_key_exists($name, $routeParams)
            ) {

                // auto inject local objects
                if ($class = $param->getClass()) {
                    $obj = strtolower(str_replace(__NAMESPACE__
                           . '\\', '', $class->getName()));
                    $params[$name] = $obj == 'server'
                                     ? $this->route->server
                                     : $this->route->server->$obj;
                } else {
                    throw new \BadMethodCallException(
                        "Required {$httpMethod} parameter \"{$name}\" missing in action.",
                        400
                    );
                }

            } elseif (isset($routeParams[$name])) {
                $params[$name] = $routeParams[$name];
            }
        }

        // TODO: maybe we need to check the order of params to match the method?

        // TODO: eventually add some kind of type casting using namespacing
        // e.g. method(integer $myInteger) => Apix\Casting\Integer, etc...
        return $params;
    }

    /**
     * Sets the entity results.
     *
     * @param  array $results
     * @return void
     */
    public function setResults(array $results=null)
    {
        $this->results = $results;
    }

    /**
     * Sets the entity results.
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
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
    * Returns the route object.
    *
    * @return Router
    */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Returns the redirect location.
     *
     * @return string
     */
    public function hasRedirect()
    {
        return isset($this->redirect);
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
     * Returns an array of method keys and action values.
     *
     * @param  array $array
     * @return array
     */
    public function getActions()
    {
        if (null === $this->actions) {
            $this->setActions();
        }

        return $this->actions;
    }

    /**
     * Returns the value of an anotation.
     *
     * @param  string   $name
     * @return mix|null
     */
    public function getAnnotationValue($name)
    {
        $method = $this->route->getMethod();
        $doc = $this->getDocs($method);

        return isset($doc[$name]) ? $doc[$name] : null;
    }

}
