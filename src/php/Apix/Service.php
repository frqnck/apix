<?php

namespace Apix;

class Service
{

    /**
     * Returns the specified service -- or all if unspecified.
     *
     * @param  string $key=null The service key to retrieve.
     * @return mixed
     */
    public static function get($key=null)
    {
        return Config::getInstance()->getServices($key);
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
        return Config::getInstance()->setService($name, $mix);
    }

}
