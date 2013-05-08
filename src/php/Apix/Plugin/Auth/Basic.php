<?php
namespace Apix\Plugin\Auth;

/**
 * HTTP Basic authentication.
 *
 * @author Franck Cassedanne
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
     * Send/set the HTTP Auth header diget
     *
     * @return void
     */
    public function send()
    {
        header('WWW-Authenticate: Basic realm="' . $this->realm . '"');
        header('HTTP/1.0 401 Unauthorized');
    }

    /**
     * Authenticate the user and return username on success.
     *
     * @link    http://uk3.php.net/manual/en/features.http-auth.php
     *
     * @return mixed Either the username of the user making the request or we
     *               return access to $this->send() which will pop up the
     *               authentication challenge once again.
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