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
        if( null !== $ctx && !($ctx instanceof \Exception) ) {
            $ctx = null;
        } 

        throw new \ErrorException($msg, 500, 0, $file, $line, $ctx);
    }

    /**
     *  Startup Exception handler
     *
     * @param \Exception $e
     * @see errorOutput
     */
    public static function startupException(\Exception $e)
    {
        self::errorOutput(
            $e->getCode(),
            (DEBUG ? 'Startup:- ' : null) . $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
    }

    /**
     *  Shutdown / Fatal error handler
     *
     * @see errorOutput
     * @codeCoverageIgnore
     */
    public static function shutdownHandler()
    {
        if ($e = error_get_last()) {
            self::errorOutput(
                $e['type'],
                (DEBUG ? 'Shutdown:- ' : null) . $e['message'],
                $e['file'],
                $e['line']
            );
        }
    }

    /**
     * Output the error.
     *
     * @param int    $code    The error number.
     * @param string $message The error message.
     * @param string $file    The filename where the error occured.
     * @param int    $line    The line number where it happened.
     * @codeCoverageIgnore
     */
    public static function errorOutput($code, $message, $file, $line)
    {
        if (!defined('UNIT_TEST')) {
            $proto = isset($_SERVER['SERVER_PROTOCOL'])
                        ? $_SERVER['SERVER_PROTOCOL']
                        : 'http:1/1';
            header($proto . ' 500 Internal Server Error', true, 500);
        }
        echo '<h1>500 Internal Server Error</h1>';
        if (DEBUG && !defined('UNIT_TEST')) {
            $info = sprintf(
                "#%d %s @ %s:%d",
                $code,
                $message,
                $file,
                $line
            );
            die($info);
        }
    }

}
