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

if (!defined('APIX_START_TIME')) {
    define('APIX_START_TIME', microtime(true));
}

class Server extends Main
{

   /**
    * POST request handler
    *
    * @param string $path The path name to match against.
    * @param mixed  $to   Callback that returns the response when matched.
    * @see  Server::proxy
    * @return Controller Provides a fluent interface.
    */
    public function onCreate($path, $to)
    {
        return $this->proxy('POST', $path, $to);
    }

    /**
     * GET request handler
     *
     * @param  string     $path The path name to match against.
     * @param  mixed      $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onRead($path, $to)
    {
        return $this->proxy('GET', $path, $to);
    }

    /**
     * PUT request handler
     *
     * @param  string     $path The path name to match against.
     * @param  mixed      $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onUpdate($path, $to)
    {
        return $this->proxy('PUT', $path, $to);
    }

    /**
     * PATCH request handler
     *
     * @param  string     $path The path name to match against.
     * @param  mixed      $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onModify($path, $to)
    {
        return $this->proxy('PATCH', $path, $to);
    }

    /**
     * DELETE request handler
     *
     * @param  string     $path The path name to match against.
     * @param  mixed      $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onDelete($path, $to)
    {
        return $this->proxy('DELETE', $path, $to);
    }

    /**
     * OPTIONS request handler
     *
     * @param  string     $path The path name to match against.
     * @param  mixed      $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onHelp($path, $to)
    {
        return $this->proxy('OPTIONS', $path, $to);
    }

    /**
     * HEAD request handler
     *
     * @param  string     $path The path name to match against.
     * @param  mixed      $to   Callback that returns the response when matched.
     * @see  Server::proxy
     * @return Controller Provides a fluent interface.
     */
    public function onTest($path, $to)
    {
        return $this->proxy('HEAD', $path, $to);
    }

   /**
    * Acts as a shortcut to resources::add.
    * @see      Resources::add
    *
    * @param    string          $method     The HTTP method to match against.
    * @param    string          $path       The path name to match against.
    * @param    mixed           $to         Callback that returns the response
    *                                       when matched.
    * @return   Controller
    */
    protected function proxy($method, $path, \Closure $to)
    {
        return $this->resources->add($path,
            array(
                'action' => $to,
                'method' => $method
            )
        );
    }

    public function setGroupInfo($path, array $docs=null)
    {
        var_dump( $this->resources); exit;
        $this->group = $infos;
    }

    /**
     * TODO: Test Read from a group.
     *
     * @param  array  $opts Options are:
     * @return string
     * @codeCoverageIgnore
     */
    public function setGroup($name, array $groupInfo=null)
    {
        $this->group[$name] = $groupInfo;
    }

}
