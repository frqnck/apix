<?php

namespace Zenya\Api\Input;

/**
 * Interface to decode inputs.
 *
 * @author Franck Cassedanne <fcassedanne@info.com>
 */
interface InputInterface
{

    /**
     * Decode an input string.
     *
     * @param   string          $string  An input string to decode.
     * @param   boolean         $assoc	 Wether to convert objects to associative arrays.
     * @return  object|array
     */
    public function decode($string, $assoc=true);

}