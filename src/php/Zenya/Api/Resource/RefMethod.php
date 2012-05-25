<?php

namespace Zenya\Api\Resource;

class RefMethod extends \ReflectionMethod
{
    /**
     * @var string|null
     */
    protected $prefix = null;

    /**
     * Holds the extracted methods comments.
     * @var array
     */
    public $docs = array();

    /**
     * Constructor
     *
     * @param string|null $prefix    [optional default:null]
     */
    public function __construct($object, $methodName, $prefix=null)
    {
        parent::__construct($object, $methodName);

		$this->prefix = $prefix;

        // extract & parse
        $this->docs = RefDoc::parseDocBook($this->getDocComment(), $prefix);
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

}