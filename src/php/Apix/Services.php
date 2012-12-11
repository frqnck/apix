<?php

namespace Apix;

class Services
{

    public static function get($key=null)
    {
        $c = Config::getInstance();
        $cb = $c->retrieve('services', $key);
        #$shared = $this->share($cb);

        return $cb();
    }

}
