<?php

namespace Zenya\Api\Resource;

class RefDoc
{
    /**
     * @var string|null
     */
    protected $prefix = null;

    /**
     * Hold the help entries.
     * @var array
     */
    protected $docs = array();

    /**
     * Constructor
     *
     * @param mixed     $mixed Either a string containing the name of the class to reflect, or an object.
     * @param string|null $prefix    [optional default:null]
     */
    function __construct(\Reflector $r, $prefix=null)
    {

        #$this->r = $r;

        #$this->r->docs = RefDoc::parseDocBook($this->r->getDocComment(), $prefix);
        #return $this->r;

        #$docs = explode(PHP_EOL, $this->getDocComment());
        #$this->class = new \ReflectionClass($mixed);
        #$this->parseDocBook( $this->class->getDocComment() );
        #$this->prefix = $prefix;
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
        /*
        if ($this->r instanceof \ReflectionMethod) {
            $this->r;       
        #} else if ($this instanceof \ReflectionMethod) {
        }
        */
        if (!array_key_exists($name, $this->docs)) {
            #$name = $this->prefix . $name;
            #if (!array_key_exists($name, $this->docs)) {
                throw new \InvalidArgumentException("Invalid element \"{$name}\"");
            #}
        }
        $prop = $this->r->getDoc($name);
        if(is_array($prop) && count($prop) == 1) {
            return reset($prop);
        } 
        return $prop;
    }

    /**
     * Get Method
     *
     * @param mixed     $mixed Either a string containing the name of the class to reflect, or an object.
     * @param string|null $prefix    [optional default:null]
     */
    function getMethod($string)
    {

        #$docs = explode(PHP_EOL, $this->getDocComment());
        $this->method = $this->class->getMethod($string);


        $this->parseDocBook( $this->class->getDocComment() );

        return $this;
    }

/* shared */

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
     * Extract docbook
     *
     * @param  string $classname
     * @return array
     */
    public static function parseDocBook($doc)
    {
        $docs = array();
        // 1. Remove /*, *, */ from the lines
        $doc = substr($doc, 3, -2);

        // 2. remove the carrier returns
        #$pattern = '/\r?\n *\* */';

        // does 1. + 2. BUT not too efficiently!
        #$pattern = '%(\r?\n(?! \* ?@))?^(/\*\*\r?\n \* | \*/| \* ?)%m';

        // same as 2. BUT keep the carrier returns in.
        $pattern = '/(\r+|\t+)? *\* */';

        $str = preg_replace($pattern, '', $doc);

       # $lines =array_map('trim',explode(PHP_EOL, $str));

        $lines = preg_split("@\r?\n|\r@", $str, null, PREG_SPLIT_NO_EMPTY); 

        // Extract the title
        $docs['title'] = array_shift($lines);

        // Extract the description
        $docs['description'] = '';
        foreach ($lines as $i => $line) {
            // extract desc
            if (strlen(trim($line)) && strpos($line, '@') !== 0) {
                $docs['description'] .= $docs['description'] ? PHP_EOL . $line : $line;
                unset($lines[$i]);
            }
        }

        // Do all the "@entries"
        preg_match_all('/@([\w_]+)\s+(.*?)\s*(?=$|@[\w_]+\s)/s', $str, $lines);

        foreach ($lines[2] as $i => $v) {
            $grp = $lines[1][$i];

            if($grp == 'param') {
                // "@param string $param description of param"
                preg_match('/(\S+)\s+\$(\S+)\s+(.+)/', $v, $m);
                $docs['params'][$m[2]] = array(
                    'type'          => $m[1],
                    'name'          => $m[2],
                    'description'   => $m[3],
                    #'required'      => $this->isReq
                );
            } else {
                // other @entries
                $docs[$grp][] = $v;
            }
        }
        return $docs;
    }

/* TODO */

    public function getSource()
    { 
        if( !file_exists( $this->getFileName() ) ) return false; 
        
        $start_offset = ( $this->getStartLine() - 1 ); 
        $end_offset   = ( $this->getEndLine() - $this->getStartLine() ) + 1; 

        return join( '', array_slice( file( $this->getFileName() ), $start_offset, $end_offset ) ); 
    } 

}