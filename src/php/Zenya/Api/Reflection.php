<?php

namespace Zenya\Api;

class Reflection
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * Holds the help entries.
     * @var array
     */
    protected $docs;

    /**
     * Holds the reflection of an entity.
     * @var array
     */
    protected $ref = array();

    /**
     * Constructor
     *
     * @param mixed       $reflected Either a string containing the name of the class to reflect, or an object.
     * @param string|null $prefix    [optional default:null]
     */
    public function __construct(Entity $entity, $prefix='null')
    {
        $this->prefix = $prefix;

        if( $entity->isClosure() ) {

            $this->ref['{group}'] = $entity->group;

            foreach($entity->getActions() as $key => $func) {
                if($func['action'] InstanceOf \Closure) {
                    $this->ref[$key] = new \ReflectionFunction($func['action']);
                }
            }

        } else {
            // assume class based
            $name = $entity->getController('name');
            $this->ref = new \ReflectionClass($name);
        }
    }

    /**
     * Parse class documentation
     *
     * @return array
     */
    public function parseDoc()
    {
        if($this->ref instanceOf \ReflectionClass) {
            $doc = $this->ref->getDocComment();
        } else {
            $doc = $this->ref['{group}']['doc'];
        }
        $this->docs = self::parsePhpDoc( $doc );
    }

    /**
     * parse a method documentation
     *
     * @param string $name A string containing the name of a method to reflect (todo: start from an object).
     */
    public function parseMethodDoc($name, $key=null)
    {
        if( $this->ref instanceOf \ReflectionClass ) {
            $method = $this->ref->getMethod($name);
            $key = null === $key ? $method->getShortName() : $key;
            $doc = $method->getDocComment();
        } else if( $this->ref[$key] instanceOf \ReflectionFunction ){
            $doc = $this->ref[$key]->getDocComment();
        } else {
          $doc = $this->ref[$key];
        }

        $this->docs['methods'][$key] =
            self::parsePhpDoc( $doc );
    }

    /**
     * Gets the documentation array
     *
     * @return array
     */
    public function getDocs($actions=null)
    {
        if(null === $this->docs) {
            $this->parseDoc();
        }

        if( isset($actions) ) {
            $actions = !is_array($actions) ? array($actions): $actions;
            foreach ($actions as $key => $method) {
                $this->parseMethodDoc($method, $key);
            }
        }

        return $this->docs;
    }

    public function getMethod($name)
    {
        return $this->ref->getMethod($name);
    }

    public function getMethods()
    {
        return $this->ref->getMethods(
            \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC
        );
    }

    /**
     * Extract PHPDOCs
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
     * Extract source code
     *
     * @param  string $classname
     * @return array
     */
    public function getSource()
    {
        if( !file_exists( $this->getFileName() ) ) return false;

        $start_offset = $this->getStartLine();
        $end_offset   = $this->getEndLine()-$this->getStartLine();

        return join('',
            array_slice(
                file($this->getFileName()),
                $start_offset-1,
                $end_offset+1
            )
        );
    }

}