<?php

namespace Zenya\Api\Input;

class Json implements Adapter
{

    /**
     * Decode a JSON string
     *
     * @param  string  $str   An XML string.
     * @param  boolean $assoc Convert objects to array.
     * @return mix
     * @see		Zenya\Api\Input\Adapter
     */
    public function decode($jsonStr, $assoc=true)
    {
        return json_decode($jsonStr, $assoc);
    }

}
