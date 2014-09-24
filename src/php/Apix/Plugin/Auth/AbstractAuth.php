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
 * @codeCoverageIgnore
 */
abstract class AbstractAuth implements Adapter
{

    /**
     * Holds the authentication realm.
     * @var string
     */
    protected $realm = null;

    /**
     * Holds the base URL.
     * @var string
     */
    protected $base_url = '/';

    /**
     * Holds the auth token.
     * @var mixed
     */
    protected $token;

    /**
     * Holds the user provided username.
     * @var string
     */
    protected $username;

    /**
     * @{@inheritdoc}
     */
    public function getToken(array $auth_data)
    {
      if ( is_callable($this->token) ) {
        $this->token = call_user_func_array($this->token, array($auth_data));
      }

      return $this->token;
    }

    /**
     * Sets the Auth token
     *
     * @return mixed $token    An auth token, can be a closure or a boolean.
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Returns the user provided username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

}
