<?php

namespace Apix\Input;

use Apix\Input\InputInterface;

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
