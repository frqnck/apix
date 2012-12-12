<?php

namespace Apix;

class Reflection
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * Constructor
     *
     * @param string|null $prefix [optional default:null]
     */
    public function __construct($prefix='null')
    {
        $this->prefix = $prefix;
    }

    /**
     * Returns prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Returns the PHPDoc string
     *
     * @param  \Reflection|string $mix A reflection object or a PHPDoc string
     * @return array
     */
    // public function getPhpDocString($mix)
    // {
    //     return $mix instanceOf \Reflector ? $mix->getDocComment() : $mix;
    // }

    /**
     * Extract PHPDOCs
     *
     * @param  \Reflection|string $mix A reflection object or a PHPDoc string
     * @return array
     */
    public static function parsePhpDoc($mix, array $requireds=null)
    {
        if ($mix instanceOf \Reflector) {
            $doc = $mix->getDocComment();
            $requireds = self::getRequiredParams($mix);
        } else {
            $doc = $mix;
        }

        $docs = array();
        // 1. Remove /*, *, */ from the lines
        $doc = substr($doc, 3, -2);

        // 2. remove the carrier returns
        #$pattern = '/\r?\n *\* */';

        // does 1. + 2. BUT not too efficiently!
        #$pattern = '%(\r?\n(?! \* ?@))?^(/\*\*\r?\n \* | \*/| \* ?)%m';

        // same as 2. BUT keep the carrier returns in.
        $pattern = '@(\r+|\t+)? *\* *@';

        $str = preg_replace($pattern, '', $doc);

       # $lines =array_map('trim',explode(PHP_EOL, $str));

        $lines = preg_split('@\r?\n|\r@', $str, null, PREG_SPLIT_NO_EMPTY);

        // extract the title
        $docs['title'] = array_shift($lines);

        // extract the description
        $docs['description'] = '';
        foreach ($lines as $i => $line) {
            if (strlen(trim($line)) && strpos($line, '@') !== 0) {
                $docs['description'] .= $docs['description'] ? PHP_EOL . $line : $line;
                unset($lines[$i]);
            }
        }

        // do all the "@entries"
        preg_match_all('/@(?P<key>[\w_]+)\s+(?P<value>.*?)\s*(?=$|@[\w_]+\s)/s', $str, $lines);

        foreach ($lines['value'] as $i => $v) {
            $grp = $lines['key'][$i];

            if ($grp == 'param') {
                // "@param string $param description of param"
                preg_match('/(?P<type>\S+)\s+\$(?P<name>\S+)(?P<description>\s+(?:.+))?/', $v, $m);

                $docs['params'][$m['name']] = array(
                #$docs['params'][] = array(
                    'type'          => $m['type'],
                    'name'          => $m['name'],
                    'description'   => isset($m['description'])
                                        ? trim($m['description'])
                                        : null,
                    'required'      => isset($requireds) && in_array($m['name'], $requireds)
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
     * Returns the required parameters
     *
     * @param  \ReflectionFunctionAbstract $ref A reflected method/function to introspect
     * @return array                       The array of required parameters
     */
    public static function getRequiredParams(\ReflectionFunctionAbstract $ref)
    {
        $params = array();
        foreach ($ref->getParameters() as $param) {
            $name = $param->getName();
            if ( !$param->isOptional() ) {
                $params[] = $name;
            }
        }

        return $params;
    }

    /**
     * Extract source code
     *
     * @param  \Reflector $ref
     * @return array
     */
    public static function getSource(\Reflector $ref)
    {
        if( !file_exists( $ref->getFileName() ) ) return false;

        $start_offset = $ref->getStartLine();
        $end_offset   = $ref->getEndLine()-$ref->getStartLine();

        return join('',
            array_slice(
                file($ref->getFileName()),
                $start_offset-1,
                $end_offset+1
            )
        );
    }

}
