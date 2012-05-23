<?php

namespace Zenya\Api\Input;

interface Adapter {

    /**
     * Data decoder.
     *
     * @param string $str An input string to convert.
     * @oaram bool    $assoc	Convert object to associative arrays.
     * @return object|array
     */
    abstract public function decode($str, $assoc=true);

}
