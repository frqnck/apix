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

class Json implements InputInterface
{

    /**
     * {@inheritdoc}
     */
    public function decode($jsonStr, $assoc=true)
    {
        return json_decode($jsonStr, $assoc);
    }

}
