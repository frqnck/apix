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
    const CRITICAL_ERROR_STRING = '500 Internal Server Error';

    /**
     *  E_RECOVERABLE_ERROR handler.
     *
     *  Use to re-throw E_RECOVERABLE_ERROR as they occur.
     *
     * @param  int             $code     The error number.
     * @param  string          $msg      The error message.
     * @param  string          $file     The filename where the error occured.
     * @param  int             $line     The line number where it happened.
     * @param  \Exception|null $previous The previous chaining Exception.
     * @throws \ErrorException
     */
    public static function errorHandler(
        $code, $msg = '', $file = __FILE__, $line = __LINE__, $previous = null
    ) {
        if (E_RECOVERABLE_ERROR == $code) {
            $msg = preg_replace('@to\s.*::\w+\(\)@', '', $msg, 1);
            // $code = 400; // Due to a client error, recoverable.
        }
        $code = 500; // Set as a HTTP Internal Server Error.

        if ( null !== $previous && !($previous instanceof \Exception) ) {

            Service::get('logger')->error(
                '{0} - {1}:{2} {3}',
                array($msg, $file, $line, $previous)
            );

            $previous = null;
        }

        throw new \ErrorException($msg, $code, 0, $file, $line, $previous);
    }

    /**
     * Startup exception handler.
     *
     * @param \Exception $e
     * @return array
     */
    public static function startupException(\Exception $e)
    {
        return self::criticalError($e, 'Startup Exception');
    }

    /**
     * Shutdown/fatal exceptions handler.
     *
     * @see criticalError
     * @return void
     * @codeCoverageIgnore
     */
    public static function shutdownHandler()
    {
        if ($e = error_get_last()) {
            self::criticalError($e, 'Shutdown Exception');
        }
    }

    /**
     * Handles critical errors (output and logging).
     *
     * @param \Exception $e
     * @return array
     */
    public static function criticalError(\Exception $e, $alt_msg) 
    {
        $err = array(
            'msg' => self::CRITICAL_ERROR_STRING,
            'ctx' => sprintf(
                        '#%d %s @ %s:%d',
                        $e->code,
                        $e->message ? $e->message : $alt_msg,
                        $e->file,
                        $e->line
                    )
        );
        printf('<h1>%s</h1>', $err['msg']);

        Service::get('logger')->critical('{msg} - {ctx}', $err);

        // @codeCoverageIgnoreStart
        // TODO: Move the following crap in the Response object...
        if (!defined('UNIT_TEST')) {
            $proto = isset($_SERVER['SERVER_PROTOCOL'])
                        ? $_SERVER['SERVER_PROTOCOL']
                        : 'HTTP/1.1';
            header($proto . ' ' . $err['msg'], true, 500);
            if(DEBUG) var_dump( $err );
        }
        // @codeCoverageIgnoreEnd

        return $err;
    }

    /**
     * Returns the provided exception as a normalize associative array.
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
            'trace'   => $e->getTraceAsString(),
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
