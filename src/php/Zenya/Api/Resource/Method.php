<?php

namespace Zenya\Api\Resource;

class Method
{
    private $refMethod;

    private $docs = array();

    private $parameters = array();

    /**
     * @var string|null
     */
    protected $prefix = null;


    public function isHidden()
    {
            $hidden = false;

        if (!$docs) {
            $hidden = true;
        }
		return $hidden;
	}

    public function __construct(\ReflectionMethod $refMethod, $prefix=null)
    {
        $this->refMethod = $refMethod;
		$this->prefix = $prefix;

        // extract & parse
        $this->extract();
        $this->parse();
	}

    /**
     * Extract docbook
     *
     * @param  string $classname
     * @return array
     */
    public function extract()
    {
		#$docs = explode(PHP_EOL, $this->refMethod->getDocComment());
		#print_r($docs);exit;

        $doc = $this->refMethod->getDocComment();

        // 1. Remove /*, *, */ from the lines
        $doc = substr($doc, 3, -2);

        // 2. remove the carrier returns
        #$pattern = '/\r?\n *\* */';

        // does 1. + 2. BUT not too efficiently!
        #$pattern = '%(\r?\n(?! \* ?@))?^(/\*\*\r?\n \* | \*/| \* ?)%m';

        // same as 2. BUT keep the carrier returns in.
        $pattern = '/\r? *\* */';

        $str = preg_replace($pattern, '', $doc);

		$lines = explode(PHP_EOL, $str);

		// Extract the description
		$desc = '';
		foreach ($lines as $i => $line) {
			// extract desc
			if (strlen($line) && strpos($line, '@') !== 0) {
				$desc .= $desc ? PHP_EOL . $line : $line;
				continue;
			}
		}
		$this->docs['description'] = $desc;

		// Do all the "@entries"
        preg_match_all('/@([a-z_]+)\s+(.*?)\s*(?=$|@[a-z_]+\s)/s', $str, $lines);

        foreach ($lines[2] as $i => $v) {
			$grp = $lines[1][$i];

			if($grp == 'param') {
				// "@param string $param description of param"
				preg_match('/(\S+)\s+\$(\S+)\s+(.+)/', $v, $m);
				$this->docs[$grp][] = array(
					'type'			=> $m[1],
					'name'			=> $m[2],
					'description'	=> $m[3]
				);
			} else {
				// other @entries
				$this->docs[$grp][] = $v;
    		}
    	}
	}

    /**
     * Parse and map the documentation entries.
     *
     * @return array
     */
    public function parse()
    {
    /*
		echo $this->description;
		echo $this->public;
		$this->listeners;
	*/
    }





    public function todo()
    {

        $this->hidden = $this->isHidden();


        $parameters = array();
        $methodname = null;
        $returns = 'mixed';
        $shortdesc = '';
        $paramcount = -1;

        // Extract info from Docblock
        $paramDocs = array();
        foreach ($docs as $i => $doc) {
            $doc = trim($doc, " \r\t/*");
            if (strlen($doc) && strpos($doc, '@') !== 0) {
                if ($shortdesc) {
                    $shortdesc .= "\n";
                }
                $shortdesc .= $doc;
                continue;
            }
            if (strpos($doc, '@xmlrpc.hidden') === 0) {
                $hidden = true;
            }

            if ((strpos($doc, '@xmlrpc.methodname') === 0) && preg_match('/@xmlrpc.methodname( )*(.*)/', $doc, $matches)) {
                $methodname = $matches[2];
            }
            
            if (strpos($doc, '@param') === 0) { // Save doctag for usage later when filling parameters
                $paramDocs[] = $doc;
            }

            if (strpos($doc, '@return') === 0) {
                $param = preg_split("/\s+/", $doc);
                if (isset($param[1])) {
                    $param = $param[1];
                    $returns = $param;
                }
            }
        }
        $this->numberOfRequiredParameters = $method->getNumberOfRequiredParameters(); // we don't use isOptional() because of bugs in the reflection API
        // Fill in info for each method parameter
        foreach ($method->getParameters() as $parameterIndex => $parameter) {
            // Parameter defaults
            $newParameter = array('type' => 'mixed');

            // Attempt to extract type and doc from docblock
            if (array_key_exists($parameterIndex, $paramDocs) &&
                preg_match('/@param\s+(\S+)(\s+(.+))/', $paramDocs[$parameterIndex], $matches)) {
                if (strpos($matches[1], '|')) {
                    $newParameter['type'] = XML_RPC2_Server_Method::_limitPHPType(explode('|', $matches[1]));
                } else {
                    $newParameter['type'] = XML_RPC2_Server_Method::_limitPHPType($matches[1]);
                }
                $tmp = '$' . $parameter->getName() . ' ';
                if (strpos($matches[3], '$' . $tmp) === 0) {
                    $newParameter['doc'] = $matches[3];
                } else {
                    // The phpdoc comment is something like "@param string $param description of param"
                    // Let's keep only "description of param" as documentation (remove $param)
                    $newParameter['doc'] = substr($matches[3], strlen($tmp));
                }
                $newParameter['doc'] = preg_replace('_^\s*_', '', $newParameter['doc']);
            }

            $parameters[$parameter->getName()] = $newParameter;
        }

        if (is_null($methodname)) {
            $methodname = $prefix . $method->getName();
        }

        $this->_internalMethod = $method->getName();
        $this->_parameters = $parameters;
        $this->_returns  = $returns;
        $this->_help = $shortdesc;
        $this->_name = $methodname;
        $this->_hidden = $hidden;
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
        $name = $this->prefix . $name;
        if (array_key_exists($name, $this->docs)) {
            if(count($this->docs[$name]) == 1) {
            	return $this->docs[$name][0];
            } 
            return $this->docs[$name];
        }
        throw new \Exception("Invalid property \"{$name}\"");
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
        return isset($this->docs[$name]);
    }

}