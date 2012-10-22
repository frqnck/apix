<?php
/**
 * Copyright (c) 2011 Franck Cassedanne, zenya.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Zenya nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Franck Cassedanne <fcassedanne@zenya.com>
 * @copyright   2011 Franck Cassedanne, zenya.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://zenya.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Zenya\Api;

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
