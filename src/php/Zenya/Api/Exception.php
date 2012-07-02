<?php
/**
 * Copyright (c) 2011 Zenya.com
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
 * @package     Zenya
 * @subpackage  ApiException
 * @author      Franck Cassedanne <fcassedanne@zenya.com>
 * @copyright   2011 zenya.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://zenya.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Zenya\Api;

class Exception extends \Exception
{

    const DEBUG = true;

    /**
     *  E_RECOVERABLE_ERROR handler
     *
     *  Throws exception occur.
     *
     * @param  int             $errno
     * @param  string          $errstr
     * @param  string          $errfile
     * @param  int             $errline
     * @return boolean
     * @throws \ErrorException
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if(self::DEBUG) return false;

        if ( E_RECOVERABLE_ERROR === $errno ) {
            $errstr = preg_replace('@to\s.*::\w+\(\)@', '', $errstr, 1);
            throw new \ErrorException($errstr, 400, 0, $errfile, $errline);
        }
        return false;
    }

    /**
     *  Fatal error handler
     *
     *  Throws exception occur.
     *
     * @param  int             $errno
     * @param  string          $errstr
     * @param  string          $errfile
     * @param  int             $errline
     * @return boolean
     * @throws \ErrorException
     */
    public static function shutdownHandler()
    {
        if ($error = error_get_last()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            echo "<h1>500 Internal Server Error</h1>";
            $info = sprintf(
                    '[SHUTDOWN] file: %s | line: %d | message: %s',
                    $error['file'],
                    $error['line'],
                    $error['message']
                );
            die( $info );
        }
    }

    public static function startupException(\Exception $e)
    {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        echo "<h1>500 Internal Server Error</h1>";
        $info = sprintf(
                "[%s]\n %s:%d %s",
                'Startup',
                $e->getFile(),
                $e->getLine(),
                $e->getMessage()
            );
        die( $info );
    }

}