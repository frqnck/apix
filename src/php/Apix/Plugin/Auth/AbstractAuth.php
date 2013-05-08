<?php
namespace Apix\Plugin\Auth;

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
     * Returns the Auth token to match against.
     *
     * @param  array                  $auth_data An array of authentification data.
     * @return boolean|string|object.
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
