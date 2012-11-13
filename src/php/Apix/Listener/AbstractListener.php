<?php
namespace Apix\Listener;

abstract class AbstractListener implements \SplObserver
{
    protected $adapter = null;

    function log($msg, $ref=null)
    {
        if(defined('DEBUG') && !defined('UNIT_TEST')) {
            $str = sprintf('%s %s (%s)', get_class($this), $msg, $ref);
            error_log( $str );
        }
    }

}