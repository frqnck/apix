<?php

namespace Zenya\Api;

class ReflectionFunc extends \ReflectionFunction
{
    /**
     * @var string|null
     */
    protected $prefix = null;

    /**
     * Holds the help entries.
     * @var array
     */
    protected $docs = array();

    /**
     * Constructor
     *
     * @param mixed       $reflected Either a string containing the name of the class to reflect, or an object.
     * @param string|null $prefix    [optional default:null]
     */
    public function __construct($mixed, $prefix=null)
    {
        $this->prefix = $prefix;

        parent::__construct($mixed);
    }

    /**
     * Parse class documentation
     *
     * @return array
     */
    public function parseClassDoc()
    {
        return $this->docs =
            self::parsePhpDoc(
                $this->getDocComment()
            );
    }

    /**
     * parse method documentation
     *
     * @param string $name A string containing the name of a method to reflect (todo: start from an object).
     */
    public function parseMethodDoc($name)
    {
        return $this->docs['methods'][ $name ] =
            self::parsePhpDoc(
                $this->getDocComment()
            );
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
     * Extract docbook
     *
     * @param  string $classname
     * @return array
     */
    public static function parsePhpDoc($str)
    {
        $docs = array();
        // 1. Remove /*, *, */ from the lines
        $doc = substr($str, 3, -2);

        // 2. remove the carrier returns
        #$pattern = '/\r?\n *\* */';

        // does 1. + 2. BUT not too efficiently!
        #$pattern = '%(\r?\n(?! \* ?@))?^(/\*\*\r?\n \* | \*/| \* ?)%m';

        // same as 2. BUT keep the carrier returns in.
        $pattern = '@(\r+|\t+)? *\* *@';

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

            if ($grp == 'param') {
                // "@param string $param description of param"
                preg_match('/(\S+)\s+\$(\S+)(\s+(.+))?/', $v, $m);

                $docs['params'][$m[2]] = array(
                    'type'          => $m[1],
                    'name'          => $m[2],
                    'description'   => isset($m[3]) ? trim($m[3]) : null,
                    #'required'      => $this->isReq
                );
            } else {
                // other @entries as group
                $docs[$grp][] = $v;
            }
        }

        //reduce group
        foreach ($docs as $key => $value) {
            if ($key !== 'params') {
                if (is_array($value) && count($value) == 1) {
                    $docs[$key] = reset( $docs[$key] );
                }
            }
        }

        return $docs;
    }

    /**
     * Returns an array of resource's methods and actions
     *
     * @param  array $array
     * @return array
     */
    public function getActionsMethods(array $array=array())
    {

        //$actions = $this->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC);
        // TODO: grab from group under same controller?
        $actions = array();

        $_actions = array();
        foreach ($actions as $action) {
            $_actions[] = $action->name;
        }

        return array_intersect($array, $_actions);
    }

    // obsolete
    // /**
    //  * Returns all the resource's methods
    //  *
    //  * @return array
    //  */
    // public function getMethodKeys()
    // {
    //     return array_keys($this->getActionsMethods());
    // }

    // obsolete
    // /**
    //  * Returns all the resource's actions
    //  *
    //  * @return array
    //  */
    // public function getActions()
    // {
    //     return array_values($this->getActionsMethods());
    // }

    /**
     * Extract source code
     *
     * @param  string $classname
     * @return array
     */
    public function getSource()
    {
        if( !file_exists( $this->getFileName() ) ) return false;

        $start_offset = ( $this->getStartLine() - 1 );
        $end_offset   = ( $this->getEndLine() - $this->getStartLine() ) + 1;

        return join( '', array_slice( file( $this->getFileName() ), $start_offset, $end_offset ) );
    }

}
