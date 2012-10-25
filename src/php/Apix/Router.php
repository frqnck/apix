<?php

namespace Apix;

/**
 * Apix Router class
 *
 * @example     $rules = array('/resource/:keyname/:id' => array('controller'=>'aController', 'action'=>'anAction'));
 *              $router = Router($rules);
 *              $router->init( $_SERVER['REQUEST_URI'] ); // execute router
 *
 * @todo    maybe add a regex parser
 *          $r = array(
 *            '@^/resource/[\w-]+/list/(.+)/$@i' =>
 *            '@^/resource/[\w-]+/list/$@i'
 *            '@^/resource/[\w-]+/$@i'
 *          );
 */
class Router
{

    /**
     * Holds the controller string.
     * @var	string
     */
    protected $controller = null;

    /**
     * Holds the current method.
     * @var string
     */
    protected $method = null;

    /**
     * Holds the current action.
     * @var	string
     */
    protected $action = null;

    /**
     * Holds the current action.
     * @var string
     */
    protected $name = null;

    /**
     * Holds all the actions (HTTP methods to CRUD verbs).
     * TODO: refactor this!!
     * @var array
     */
    public static $actions = array(
        'POST'      => 'onCreate',
        'GET'       => 'onRead',
        'PUT'       => 'onUpdate',
        'DELETE'    => 'onDelete',
        'PATCH'     => 'onModify',
        'OPTIONS'   => 'onHelp',
        'HEAD'      => 'onTest',
        'TRACE'     => 'onTrace'
    );

    /**
     * Holds an array of router params
     * @var	array
     */
    protected $params = array();

    private $_rules = array();
    private $_defaults = array();

    /**
     * The constructor, set the default routing rules.
     *
     * @param  array                     $rules
     * @param  array                     $defaults
     * @throws \InvalidArgumentException
     */
    public function __construct(array $rules=array(), array $defaults=array())
    {
        foreach ($rules as $key => $rule) {
            if ( is_int($key) ) {
                throw new \InvalidArgumentException("Invalid rules array specified (not associative)");
            }
            $this->_rules[$key] = $rule;
        }

        // merges defaults with required props
        $this->_defaults = $defaults+array('controller'=>null,'action'=>null);

        // set default properties
        foreach ($this->_defaults as $prop => $value) {
            $this->$prop = $value;
        }
    }

    /**
     * Sets the public properties e.g. controller, action, method and params
     * in order as per the following:
     *      - router rules,
     *      - router params,
     *      - then router defauls.
     *
     * @param  string $route
     * @param  array  $rules
     * @param  array  $params
     * @return void
     */
    public function setAsProperties(array $rules, array $params)
    {
        foreach (array_keys($this->_defaults) as $k) {
            $value = isset($rules[$k]) ? $rules[$k]	// rules
                : (isset($params[$k]) ? $params[$k]	// params
                : $this->_defaults[$k]);			// defaults

            if (property_exists($this, $k)) {
                $this->$k = $value;
            }
            // else {
            //     echo 'TEMP';
            //     $this->temp->$k = $value;
            // }
        }
        $this->params = $params;
    }

    /**
     * Maps an URI against the routing table.
     *
     * @param  string $uri
     * @param  array  $params Additional params to merge with the current set (optional)
     * @return void
     */
    public function map($uri, array $params=null)
    {
        if (!is_null($params)) {
            // merge with existing, precedence!
            $this->setParams( $this->params+$params );
        }

        foreach ($this->_rules as $route => $rules) {
            $params = $this->routeToParamsMatcher($route, $uri);
            if ($uri == $route || $params) {
                $this->name = $route;

                if(is_object($rules)) $rules = $rules->toArray();

                return $this->setAsProperties($rules, $params);
            }
        }
    }

    /**
     * Tries to match a route to an URI params.
     *
     * @param  string $route
     * @param  string $uri
     * @return array
     */
    public function routeToParamsMatcher($route, $uri)
    {
        $bits = explode('/', $route);
        $paths = explode('/', $uri);
        $result = array();

        // match 1st URI element not a param
        if (count($paths) == 2 && count($bits) >2 ) {
            if ($paths[1] == $bits[1]) {
                return array($paths[1]);
            }
        }

        // params
        foreach ($bits as $key => $value) {
            if (preg_match('/^:[\w]+$/', $value)) {
                if (isset($paths[$key])) {
                    $value = substr($value, 1); // rm ':'
                    $result[$value] = $paths[$key];
                }
            } elseif (!isset($paths[$key]) || strcmp($value, $paths[$key]) != 0) {
                return false;
            }
        }

        return $result;
    }

    /**
     * Sets the current action from a specified method
     * or use the method in the current scope.
     *
     * @param  string $method The method to set the action (optional)
     * @return void
     */
    public function setAction($method=null)
    {
        if (!is_null($method)) {
            $this->setMethod($method);
        }
        $this->action = isset(self::$actions[$this->method])
            ? self::$actions[$this->method]
            : null;
    }

    /**
     * Returns the current action, or as specified.
     *
     * @param  string $method A method key (optional)
     * @return string
     */
    public function getAction($method=null)
    {
        if (isset($method)) {
            return isset(self::$actions[$method]) ? self::$actions[$method] : null;
        }
        if (null === $this->action) {
            $this->setAction();
        }

        return $this->action;
    }

    /**
     * Returns all the actions.
     *
     * @return array
     */
    public function getActions()
    {
        return self::$actions;
    }

    /**
     * Sets the router's params.
     *
     * @param  array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Returns all the router's params.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the specified router param.
     *
     * @param  string $key
     * @param  string $value
     * @return void
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Returns the specified router param.
     *
     * @param  string                    $key
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getParam($key)
    {
        if (isset($this->params[$key])) {
            return $this->params[$key];
        }

        throw(new \InvalidArgumentException(sprintf('Invalid parameter "%s" requested.', $key)));
    }

    /**
     * Checks a specified router param exists.
     *
     * @param  string $key A key to check
     * @return bolean
     */
    public function hasParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * Sets the controller.
     *
     * @param  string $controller
     * @return void
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Returns the current controller.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Sets the method.
     *
     * @param  string $method
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Returns the current method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns, if set, the current route name.
     * Alternatively, return the current path in scope.
     *
     * @return string|null
     */
    public function getName()
    {
        return isset($this->name) ? $this->name
            : ( isset($this->path) ? $this->path : null );
    }

    /**
     * Sets the route name.
     *
     * @param  $name
     * @return string|null
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}
