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

namespace Apix\Plugin\Auth;

/**
 * HTTP Basic authentication.
 *
 * @author Franck Cassedanne
 * @codeCoverageIgnore
 */
class Basic extends AbstractAuth
{

    /**
     * Constructor
     *
     * @param string|null $realm
     */
    public function __construct($realm = null)
    {
        $this->realm = null !== $realm
                     ? $realm
                     : $_SERVER['SERVER_NAME'];
    }

    /**
     * @{@inheritdoc}
     */
    public function send()
    {
        header('WWW-Authenticate: Basic realm="' . $this->realm . '"');
        header('HTTP/1.0 401 Unauthorized');
    }

    /**
     * @{@inheritdoc}
     *
     * @link    http://uk3.php.net/manual/en/features.http-auth.php
     */
    public function authenticate()
    {
        if (
            isset($_SERVER['PHP_AUTH_USER'])
        ) {
            $this->username = $_SERVER['PHP_AUTH_USER'];

            $user = array(
                'username' => $_SERVER['PHP_AUTH_USER'],
                'password' => $_SERVER['PHP_AUTH_PW']
            );
            if (true === $this->getToken($user)) {
                return true;
            }
        }

        return $this->send();
    }

}
