<?php
namespace Apix\Plugins\Auth;

abstract class AbstractAuth implements Adapter
{

    /**
     * @var boolean Holds
     */
    public $token = null;

    /**
     * Returns the auth token
     *
     * @param array The digest/basic array
     * @return string The token to match with the digest password
     */
    public function getToken(array $data)
    {
      if ( is_callable($this->token) ) {
        $token = call_user_func_array($this->token, array($data));
        $this->setToken($token);
      }

      return $this->token;
    }

    /**
     * Sets the auth token
     *
     * @return string The token to macth with the digest password
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

}