<?php
namespace Apix\Listener;

abstract class AbstractListener implements \SplObserver
{
    protected $adapter;

    private $_subtags_extract = null;

    /**
     * Returns the value of the specifified subtag.
     *
     * @param  string       $key        The $key to retrieve.
     * @param  string|null  $default    The default value. 
     * @return array|null               An indexed array of values, or null.
     */
    public function getSubTagValues($key, array $default=null)
    {
        $tags = $this->extractSubTags();
        $k = array_search($key, $tags['keys']);
        return false === $k ? $default : explode(',', $tags['values'][$k]);
    }

    /**
     * Extracts all the subtags.
     *
     * @return array An associative array
     */
    public function extractSubTags()
    {
        if($this->_subtags_extract === null) {
            $lines = $this->entity->getAnnotationValue($this->annotation);
            if(!is_array($lines)) $lines = array($lines);
            foreach($lines as $line) {
                preg_match_all('/(?P<keys>[^=\s]+)=(?P<values>[^=\s]+)/i', $line, $this->_subtags_extract);
            }
        }
        return $this->_subtags_extract;
    }

    function log($msg, $ref=null)
    {
        if(defined('DEBUG') && !defined('UNIT_TEST')) {
            $str = sprintf('%s %s (%s)', get_class($this), $msg, $ref);
            error_log( $str );
        }
    }

}