<?php

namespace Apix;

class Exception extends \Exception
{

    const DEBUG =  true;

    /**
     *  E_RECOVERABLE_ERROR handler
     *
     *  Use to re-throw E_RECOVERABLE_ERROR as they occur.
     *
     * @param  int             $code    The error number.
     * @param  string          $message The error message.
     * @param  string          $file    The filename where the error occured.
     * @param  int             $line    The line number at which the error happened.
     * @param  array           $context The array of context vars.
     * @throws \ErrorException
     * @return false
     */
    public static function errorHandler($code, $message, $file, $line, $context)
    {
        if(self::DEBUG) return false;

        if (E_RECOVERABLE_ERROR === $code) {
            $message = preg_replace('@to\s.*::\w+\(\)@', '', $message, 1);
            throw new \ErrorException($message, 400, 0, $file, $line, $context);
        }

        return false;
    }

    /**
     *  Startup Exception handler
     *
     * @param \Exception $e
     * @see errorOutput
     */
    public static function startupException(\Exception $e)
    {
        self::errorOutput($e->getCode(), 'Startup:- ' . $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     *  Shutdown / Fatal error handler
     *
     * @see errorOutput
     */
    public static function shutdownHandler()
    {
        if ($e = error_get_last()) {
            self::errorOutput($e['type'], 'Shutdown:- ' . $e['message'], $e['file'], $e['line']);
        }
    }

    /**
     * Output the error
     *
     * @param  int             $code    The error number.
     * @param  string          $message The error message.
     * @param  string          $file    The filename where the error occured.
     * @param  int             $line    The line number at which the error happened.
     * @param  array           $context The array of context vars.
     * @throws \ErrorException
     * @return false
     */
    public static function errorOutput($code, $message, $file, $line, $context = null)
    {
        $proto = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'http:1/1';
        if (!defined('UNIT_TEST')) {
            header($proto . ' 500 Internal Server Error', true, 500);
        }
        echo '<h1>500 Internal Server Error</h1>';

        if (self::DEBUG) {
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
