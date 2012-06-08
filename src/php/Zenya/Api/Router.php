<?php
/*
    $rules = array(
        '/books/:id/:keyname' => array('controller'=>'books', 'action'=>'view')
    );

    $router = Router($rules);
    $router->init( $_SERVER['REQUEST_URI'] ); // execute router

    print_r($router);
*/
namespace Zenya\Api;

# test routing
/*
    TODO: add a regex parser
    $r = array(
        '@^/users/[\w-]+/bookmarks/(.+)/$@i' =>
        '@^/users/[\w-]+/bookmarks/$@i'
        '@^/users/[\w-]+/$@i'
    );
*/

class Router
{

    /**
     * Holds the controller string.
     * @var	string
     */
    public $controller = null;

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
     * Holds all the actions.
     * @var array
     */
    protected $actions = array();

    /**
     * Holds the array of params.
     * @var	array
     */
    public $params = array();

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
     * @param array $rules
     * @param array $defaults
     * @throws \InvalidArgumentException 500
     */
    public function __construct(array $rules, array $defaults=array())
    {
        foreach ($rules as $k => $v) {
            if ( is_int($k) ) {
                throw new \InvalidArgumentException("Invalid rules array specified (not associative)", 500);
            }
            $this->_rules[$k] = $v;
        }

        // merges defaults with required props
        $this->_defaults = $defaults+array('controller'=>null,'action'=>null);

        // set default properties
        foreach ($this->_defaults as $k => $v) {
            $this->$k = $v;
        }

        // Array of HTTP methods to CRUD verbs.
        $this->actions = array(
            'POST'      => 'onCreate',
            'GET'       => 'onRead',
            'PUT'       => 'onUpdate',
            'DELETE'    => 'onDelete',
            'OPTIONS'   => 'onHelp',
            'HEAD'      => 'onTest',
            'TRACE'     => 'onTrace'
        );

    }

     /**
     * Set the public properties such as controller, action and params
     *
     * @param  array $rules
     * @param  array $params
     * @return void
     */
    public function setMainProperties(array $rules, array $params)
    {
        foreach (array_keys($this->_defaults) as $k) {
            $this->$k = isset($rules[$k])?$rules[$k]	// rules
                : (isset($params[$k])?$params[$k]		// params
                : $this->_defaults[$k]);				// defaults
        }
        $this->params = $params;
    }

       /**
     * Url mapper
     *
     * @param  string $url
     * @return void
     */
    public function map($url, array $params=null)
    {
        if(!is_null($params)) {
            // merge with exisitin, precedence!
            $this->setParams( $this->params+$params );
        }
        foreach ($this->_rules as $k => $rules) {
            $params = $this->ruleMatch($k, $url);
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
        foreach ($ruleItems as $k => $v) {
            if (preg_match('/^:[\w]{1,}$/', $v)) {
                $v = substr($v,1);
                if (isset($paths[$k])) {
                    $result[$v] = $paths[$k];
                }
            } else {
                if (strcmp($v, $paths[$k]) != 0) {
                    return false;
                }
            }
        }

        return $result;
    }

    /**
     * Get actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }
    
    /**
     * Get params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set params
     *
     * @return void
     */
    public function setParams(array $params)
    {
        return $this->params = $params;
    }

    /**
     * Set the action based on the current method or passed $method string.
     *
     * @param string $method
     * @return void
     */
    public function setAction($method=null)
    {
        if(!is_null($method)) {
            $this->method = $method;
        }
        $this->action = isset($this->actions[$this->method])
            ? $this->actions[$this->method]
            : null;
    }

    /**
     * Get the controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        return $this->controller;
    }

    /**
     * Set the controller
     *
     * @return string
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get the action name
     *
     * @return string
     */
    public function getAction()
    {
        if (null === $this->action) {
            $this->setAction();
        }

        return $this->action;
    }

    /**
     * Get method name
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the method
     *
     * @return string
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

}