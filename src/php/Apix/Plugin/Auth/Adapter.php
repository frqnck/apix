<?php
namespace Apix\Plugin\Auth;

interface Adapter
{

    /**
     * Performs an authentication attempt
     *
     * @return boolean
     */
    public function authenticate();

}
