<?php

namespace Zenya\Api;

class Services //extends Config
{

    public static function get($key=null)
    {
        $c = Config::getInstance();
        $cb = $c->retrieve('services', $key);
        #$shared = $this->share($cb);

        return $cb();
    }

}
