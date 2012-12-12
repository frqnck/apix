<?php
namespace Apix\Plugin\Auth;

abstract class AbstractAuth implements Adapter
{

    /**
     * @var mixed Holds an auth token.
     */
    protected $token;

    /**
     * Returns an auth token to match against.
     *
     * @param  array           $auth_data An array of authentification data.
     * @return boolean|string.
     */
    public function getToken(array $auth_data)
    {
      if ( is_callable($this->token) ) {
        $this->token = call_user_func_array($this->token, array($auth_data));
      }

      return $this->token;
    }

    /**
     * Sets an auth token
     *
     * @return mixed $token    An auth token, can be a closure or a boolean.
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

}
