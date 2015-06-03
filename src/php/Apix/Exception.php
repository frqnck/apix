<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix;

use Apix\Service;

class Exception extends \Exception
{

    /**
     *  E_RECOVERABLE_ERROR handler
     *
     *  Use to re-throw E_RECOVERABLE_ERROR as they occur.
     *
     * @param  int              $code The error number.
     * @param  string           $msg  The error message.
     * @param  string           $file The filename where the error occured.
     * @param  int              $line The line number where it happened.
     * @param  array|\Exception $ctx  The context array or previous Exception.
     * @throws \ErrorException
     */
    public static function errorHandler(
        $code, $msg='', $file=__FILE__, $line=__LINE__, $ctx=null
    ) {
        if (E_RECOVERABLE_ERROR == $code) {
            $msg = preg_replace('@to\s.*::\w+\(\)@', '', $msg, 1);
            // $code = 400;
        }
        if ( null !== $ctx && !($ctx instanceof \Exception) ) {
            $ctx = null;
        }

        throw new \ErrorException($msg, 500, 0, $file, $line, $ctx);
    }

    /**
     *  Startup Exception handler
     *
     * @param \Exception $e
     * @see criticalError
     */
    public static function startupException(\Exception $e)
    {
        self::criticalError(
            $e->getCode(),
            (DEBUG ? 'Startup: ' : null) . $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }

    /**
     *  Shutdown / Fatal error handler
     *
     * @see criticalError
     * @codeCoverageIgnore
     */
    public static function shutdownHandler()
    {
        if ($e = error_get_last()) {
            self::criticalError(
                $e['type'],
                (DEBUG ? 'Shutdown: ' : null) . $e['message'],
                $e['file'],
                $e['line']
            );
        }
    }

    /**
     * Critical Error Output and log.
     *
     * @param int    $code    The error number.
     * @param string $message The error message.
     * @param string $file    The filename where the error occured.
     * @param int    $line    The line number where it happened.
     * @codeCoverageIgnore
     */
    public static function criticalError($code, $message, $file, $line)
    {
        $msg = '500 Internal Server Error';
        if (!defined('UNIT_TEST')) {
            $proto = isset($_SERVER['SERVER_PROTOCOL'])
                        ? $_SERVER['SERVER_PROTOCOL']
                        : 'http:1/1';
            header($proto . ' ' . $msg, true, 500);
        }
        printf('<h1>%s</h1>', $msg);

        $info = sprintf("#%d %s @ %s:%d", $code, $message, $file, $line);
        Service::get('logger')->critical('{msg} [{info}]',
            array('msg'=>$msg, 'info'=>$info)
        );

        if (DEBUG && !defined('UNIT_TEST')) {
            die(array($info));
        }
    }

    /**
     * Returns this exception as an associative array.
     *
     * @param  \Exception $e
     * @return array
     */
    public static function toArray(\Exception $e)
    {
        $array = array(
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
            'type'    => get_class($e),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'stack trace'   => $e->getTraceAsString(),
        );

        if (method_exists($e, 'getPrevious')) {
            $p = $e->getPrevious();
            if (method_exists($p, 'getTraceAsString')) {
                $array['prev'] = $p->getTraceAsString();
            }
        }

        return $array;
    }

}
