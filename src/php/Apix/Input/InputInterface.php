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

namespace Apix\Input;

/**
 * Interface to decode inputs.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
interface InputInterface
{

    /**
     * Decode an input string.
     *
     * @param  string       $string An input string to decode.
     * @param  boolean      $assoc  Wether to convert objects to associative arrays.
     * @return object|array
     */
    public function decode($string, $assoc=true);

}
