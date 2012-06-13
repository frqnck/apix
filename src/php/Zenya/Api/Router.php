<?php

namespace Zenya\Api;

/**
 * Router class
 *
 * @example     $rules = array('/resource/:keyname/:id' => array('controller'=>'aController', 'action'=>'anAction'));
 *              $router = Router($rules);
 *              $router->init( $_SERVER['REQUEST_URI'] ); // execute router
 *
 * @todo    add a regex parser
 *          $r = array(
 *            '@^/resource/[\w-]+/list/(.+)/$@i' =>
 *            '@^/resource/[\w-]+/list/$@i'
 *            '@^/resource/[\w-]+/$@i'
 *          );
 */
class Router
{

    /**
     * Holds the controller string
     * @var	string
     */
    protected $controller = null;

    /**
     * Holds the current method
     * @var string
     */
    protected $method = null;

    /**
     * Holds the current action
     * @var	string
     */
    protected $action = null;

    /**
     * Holds all the actions (HTTP methods => CRUD verbs)
     * @var array
     */
    protected $actions = array(
        'POST'      => 'onCreate',
        'GET'       => 'onRead',
        'PUT'       => 'onUpdate',
        'DELETE'    => 'onDelete',
        'OPTIONS'   => 'onHelp',
        'HEAD'      => 'onTest',
        'TRACE'     => 'onTrace'
    );

    /**
     * Holds an array of router params
     * @var	array
     */
    protected $params = array();

    /**
     * @var	array
     */
    private $_rules = array();

    /**
     * @var	array
     */
    private $_defaults = array();

    /**
     * Constructor
     *
     * @param  array                     $rules
     * @param  array                     $defaults
     * @throws \InvalidArgumentException 500
     */
    public function __construct(array $rules=array(), array $defaults=array())
    {
        foreach ($rules as $key => $rule) {
            if ( is_int($key) ) {
                throw new \InvalidArgumentException("Invalid rules array specified (not associative)", 500);
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
     * @param  array $rules
     * @param  array $params
     * @return void
     */
    public function setMainProperties(array $rules, array $params)
    {
        foreach (array_keys($this->_defaults) as $k) {
            $value = isset($rules[$k]) ? $rules[$k]	// rules
                : (isset($params[$k]) ? $params[$k]	// params
                : $this->_defaults[$k]);			// defaults

            if (property_exists($this, $k)) {
                $this->$k = $value;
            } else {
                echo 'TEMP';
                $this->temp->$k = $value;
            }
        }
        $this->params = $params;
    }

    /**
     * Url mapper
     *
     * @param  string $uri
     * @param  array  $params Additional params to merge with the current set(optional)
     * @return void
     */
    public function map($uri, array $params=null)
    {
        if (!is_null($params)) {
            // merge with exisitin, precedence!
            $this->setParams( $this->params+$params );
        }
        foreach ($this->_rules as $k => $rules) {
            $params = $this->ruleMatch($k, $uri);
            if ($params) {
                $this->setMainProperties($rules, $params);

                return;
            }
        }
    }

    /**
     * Rule matcher...
     *
     * @param  string $rule
     * @param  string $url
     * @return array
     */
    public function ruleMatch($rule, $url)
    {
        $ruleItems = explode('/', $rule);
        $paths = explode('/', $url);
        $result = array();
        foreach ($ruleItems as $key => $value) {
            if (preg_match('/^:[\w]{1,}$/', $value)) {
                $value = substr($value, 1);
                if (isset($paths[$key])) {
                    $result[$value] = $paths[$key];
                }
            } else {
                if (!isset($paths[$key]) || strcmp($value, $paths[$key]) != 0) {
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * Sets the current action using the current or a specified method
     *
     * @param  string $method The method to set the action (optional)
     * @return void
     */
    public function setAction($method=null)
    {
        if (!is_null($method)) {
            $this->setMethod($method);
        }
        $this->action = isset($this->actions[$this->method])
            ? $this->actions[$this->method]
            : null;
    }

    /**
     * Returns the current or specified action
     *
     * @param  string $method A method key (optional)
     * @return string
     */
    public function getAction($method=null)
    {
        if (isset($method)) {
            return isset($this->actions[$method]) ? $this->actions[$method] : null;
        }
        if (null === $this->action) {
            $this->setAction();
        }

        return $this->action;
    }

    /**
     * Returns all the actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Sets the router's params
     *
     * @param  array $params
     * @return void
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Returns all the router's params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the specified router param
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
     * Returns the specified router param
     *
     * @param  string $key
     * @return array
     */
    public function getParam($key)
    {
        return $this->params[$key];
    }

   /**
     * Checks if specified router param exist
     *
     * @param  string $key A key to check
     * @return bolean
     */
    public function hasParam($key)
    {
        return isset($this->params[$key]);
    }

    /**
     * Sets the controller name
     *
     * @param  string $controller
     * @return void
     */
    public function setControllerName($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Returns the controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * Sets the method
     *
     * @param  string $method
     * @return void
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Returns the method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

}
