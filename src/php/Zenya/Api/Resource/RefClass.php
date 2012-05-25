<?php

namespace Zenya\Api\Resource;

class RefClass extends \ReflectionClass
{
    /**
     * @var string|null
     */
    protected $prefix = null;

    /**
     * Holds the extracted class comments.
     * @var array
     */
    protected $docs = array();

    /**
     * Constructor
     *
     * @param mixed     $mixed Either a string containing the name of the class to reflect, or an object.
     * @param string|null $prefix    [optional default:null]
     */
    public function __construct($mixed, $method, $prefix=null)
    {
        parent::__construct($mixed);

        $this->prefix = $prefix;

        // extract & parse
        $this->docs = RefDoc::parseDocBook($this->getDocComment());
    }

    /**
     * Returns the full docbook array.
     *
     * @return array
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * Returns the value for a named parameter, returns null if it does not exist.
     *
     * The magic __get method works in the context of naming the option
     * as a virtual member of this class.
     *
     * @param  string      $key
     * @return string
     */
    public function getDoc($name)
    {
        if (!array_key_exists($name, $this->docs)) {
            #$name = $this->prefix . $name;
            #if (!array_key_exists($name, $this->docs)) {
                throw new \InvalidArgumentException("Invalid element \"{$name}\"");
            #}
        }
        $prop = $this->docs[$name];
        if(is_array($prop) && count($prop) == 1) {
            return reset($prop);
        } 
        return $prop;
    }



    /**
     * Return array representation.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->docs;
    }

    /**
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
        if (array_key_exists($name, $this->docs)) {
            return $this->docs[$name];
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
