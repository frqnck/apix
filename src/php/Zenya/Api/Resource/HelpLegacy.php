<?php

/**
 * Zenya_Api_ManualParser
 *
 * e.g.
 *
 * @api_description This service provides API documentation for each
 *
 * @api_method GET
 * @api_method OPTIONS
 *
 * @api_param ParamName (datatype version group) A detailled description of the parameter.
 *
 * @api_param datatype (string|array 1.0 required) The datatype returned.
 * @api_param version (string|array 1.0 required) The version number from of introduction (since)
 * @api_param group (integer 1.0 optional) Wethr required or optional (use a dash '-' to leave blank)
 *
 * @api_param default (string|integer|array 1.1 optional) Show default value.
 *
 * @api_param string (string 1.0 -) A string represents text or an array of character.
 * @api_param boolean (boolean 1.0 -) A boolean can be either true or false.
 * @api_param integer (integer 1.0 -) An integer is a 32-bit basic integral type.
 * @api_param timestamp (timestamp 1.0 -) A timestamp represents both date and time formated as 'YYYY-MM-DD hh:mm:ss UTC'.
 *
 * @api_response_param session_id (string 1.0 always) The session_id string that needs to be passed to other API methods
 *
 * @api_public true
 * @api_version 1.0
 *
 * @api_exception_code 404
 *
 * @test This is just to test parsing of this '"| and that: *&/ and \ ?! ***
 * @test Testing UTF-8: £ $ @ é
 *
 * @author      Franck Cassedanne <franck@info.com>
 * @copyright   Copyright (c) 2010 Info.com Ltd. (http://www.info.com)
 * @version     $Id$
 */
/*
  Required, optional, and key parameters
  A command can have parameters that must be coded (required parameters) and parameters that do not have to be coded (optional parameters). Optional parameters are usually assigned a system-defined default value if another value is not specified for the parameter when the command is entered.
  A command can also have key parameters which are the only parameters shown on the display when a user prompts for the command. After values are entered for the key parameters, the remaining parameters are shown with actual values instead of the default values (such as *SAME or *PRV).
  #$str = 'ParamName (datatype,requirement,since) Detailled description.';
  # http://aql.com/telecoms/999-services/999-hosted-numbers/

 */
class Zenya_Api_ManualParser
{

    /**
     * @var string
     */
    public $show_debug = false;

    /**
     * @var string
     */
    public $classname;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    protected $prefix = null;

    /**
     * Hold the raw extraction of the class comments.
     * @var array
     */
    protected $raw = array();

    /**
     * Hold the help entries.
     * @var array
     */
    protected $helps = array();

    /**
     * Constructor
     *
     * @param string      $classname
     * @param string      $title
     * @param string|null $prefix    [optional default:null]
     */
    public function __construct($classname, $title, $prefix=null)
    {
        $this->classname = $classname;
        $this->title = ucfirst($title);
        $this->prefix = $prefix;

        // extract & parse
        $this->extract($classname);
        $this->parse();
    }

    /**
     * Extract docbook comments from a classname.
     *
     * @param  string $classname
     * @return array
     */
    public function extract($classname)
    {
        $rc = new ReflectionClass($classname);
        $doc = $rc->getDocComment();

        // 1.) Remove /*, *, */ in front of each lines
        $doc = substr($doc, 3, -2);

        // 2.) remove the carrier returns
        #$pattern = '/\r?\n *\* */';

        // does 1.) + 2) but not efficiently!
        #$pattern = '%(\r?\n(?! \* ?@))?^(/\*\*\r?\n \* | \*/| \* ?)%m';


        // same as 2.) but keep the carrier returns in.
        $pattern = '/\r? *\* */';

        $str = preg_replace($pattern, '', $doc);

        preg_match_all('/@([a-z_]+)\s+(.*?)\s*(?=$|@[a-z_]+\s)/s', $str, $matches);

        foreach ($matches[2] as $k => $v) {
            $key = $matches[1][$k];
            $this->raw[$key][] = $v;
        }

    }

    /**
     * Parse and map the documentation entries.
     *
     * @return array
     */
    public function parse()
    {
        $map = array(
            'description',
            #'methods_old' => $this->getRawStrings('method'),
            'methods' => $this->parseMethods($this->getRawStrings('method')),
            'request_params' => $this->parseParams('param'),
            'response_params' => $this->parseParams('response_param'),
            'version',
            'public',
            'errors' => $this->mapExceptions()
        );
        foreach ($map as $key => $value) {
            $help_key = !is_int($key) ? $key : $value;
            $this->helps[$help_key] = !is_int($key) ? $value : $this->getRawStringFromKey($value, 0);
        }
    }

    /**
     * Return array representation.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->helps;
    }

    /*
     * Get the array of strings from a specified name.
     *
     * @param  string      $name
     * @return array|false array of strings, or false if ot does not exist.
     */
    protected function getRawStrings($name)
    {
        return isset($this->raw[$this->prefix . $name])
            ? $this->raw[$this->prefix . $name]
            : false;
    }

    /**
     * Get the single string from a specified name.
     *
     * @param  string $name
     * @param  string $index
     * @return string String or set it as "undifened ($name)".
     */
    protected function getRawStringFromKey($name, $index)
    {
        $key = $this->getRawStrings($name);

        return isset($key[$index]) ? $key[$index] : "undefined ($name@$index)";
    }

    /**
     * Parse the methods
     *
     * @param  array $array
     * @return array
     */
    protected function parseMethods(array $array)
    {
        $r = array();
        foreach ($array as $v) {
            $extract = explode(' ', $v, 2);
            $k = $extract[0];
            $r[$k] = $extract[1];
        }

        return $r;
    }

    /**
     * Parse parameter entries.
     *
     * @param  string $key
     * @return array
     */
    protected function parseParams($key)
    {
        $p = array();
        $strings = $this->getRawStrings($key);
        if ($strings === false) {
            return null;
        }
        foreach ($strings as $v) {
            #preg_match_all('%^(.*)\s\((.*) (.*) (.*)\)\s(.*)$%m', $v, $m, PREG_SET_ORDER);
            #$keys = array('debug', 'name', 'datatype', 'version', 'group', 'description');

            preg_match_all('%^(?:(.*)(?:\:))?(.*)\s\((.*) (.*) (.*)\)\s(.*)$%m', $v, $m, PREG_SET_ORDER);
            $keys = array('debug', 'methods', 'name', 'datatype', 'version', 'group', 'description');
            $p[] = array_combine($keys, $m[0]);
        }

        return $this->regroupByKey($p, 'group');
    }

    /**
     * Regroup the params by the group key.
     *
     * @param array $params Array of the params
     * $param string $key [optional default:'group'] The key to group upon, default set to 'group'.
     * @return array
     */
    protected function regroupByKey(array $params, $key='group')
    {
        $array = array();
        foreach ($params as $k => $v) {
            $group = $v[$key];
            if ($this->show_debug === false) {
                unset($v['debug']);
                unset($v['group']);
            }
            $array[$group][] = $v;
        }

        return $array;
    }

    /**
     * Map the exception_code to their string equivalent.
     *
     * @return array
     */
    protected function mapExceptions()
    {
        $exceptions = $this->getRawStrings('exception_code');
        if (empty($exceptions))

            return null;
        $errs = array();
        foreach ($exceptions as $k) {
            $errs[$k] = "TODO: Zenya_Api_Exception::$k";
        }

        return $errs;
    }

    /**
     * Return the value for a named parameter, returns null if it does not exist.
     *
     * The magic __get method works in the context of naming the option
     * as a virtual member of this class.
     *
     * @param  string      $key
     * @return string|null
     */
    public function __get($name)
    {
        #$name = $this->prefix . $name;
        if (array_key_exists($name, $this->helps)) {
            return $this->helps[$name];
        }
        throw new Zenya_Api_Exception("Invalid property \"{$name}\"");
    }

    /**
     * Test whether a given parameters is set.
     *
     * @param  string  $name
     * @return boolean
     */
    public function __isset($name)
    {
        $name = $this->prefix . $name;

        return isset($this->raw[$name]);
    }

}
