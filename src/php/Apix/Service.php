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

class Service
{

    /**
     * Returns the specified service -- or all if unspecified.
     *
     * @param  string     $key=null  The service key to retrieve.
     * @param  array|null $args=null An array of argument to pass to the service.
     * @return mixed
     */
    public static function get($key=null, $args=null)
    {
        return Config::getInstance()->getServices($key, $args);
    }

    /**
     * Sets the specified name, value as a service.
     *
     * @param  string $name The service name to set.
     * @param  mixed  $mix  The corresponding value to set.
     * @return void
     */
    public static function set($name, $mix)
    {
        Config::getInstance()->setService($name, $mix);
    }

    /**
     * Checks wether the named service exists or not.
     *
     * @param  string  $name The service name to look for.
     * @return boolean
     */
    public static function has($name)
    {
        $services = Config::getInstance()->get('services');

        return isset($services[$name]);
    }

    /**
     * Checks wether the named service exists or not.
     *
     * @param  string  $name The service name to look for.
     * @return boolean
     */
    public static function debug($key=null)
    {
        echo '<pre>';
        var_dump( Config::getInstance()->getServices($key) );
        echo '</pre>';
    }

}
