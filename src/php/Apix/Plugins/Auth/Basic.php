<?php
namespace Apix\Plugins\Auth;

/*
Example usage

$HTTPDigest =& new Digest();
if (
        $username = $HttpDigest->authenticate(
            array(
            'username' => md5('username:'.$HTTPDigest->getRealm().':password')
            )
        )
    ) {
        echo sprintf('Logged in as "%s"', $username);
} else {
    $HTTPDigest->send();
    echo 'Not logged in';
}
*/

/**
 * HTTP Digest authentication class
 *
 * @link http://www.peej.co.uk/files/httpdigest.phps
 */
class Basic extends AbstractAuth
{
    /**
     * @var string The authentication realm.
     */
    public $realm = null;

    /**
     * @var string The base URL of the application.
     */
    public $baseURL = '/';

    /**
     * Constructor
     *
     * The constructor that sets the $this->realm
     *
     * @param string $realm Perhaps a custom realm. Default is null so the
     *                      realm will be $_SERVER['SERVER_NAME']
     */
    public function __construct($realm = null)
    {
        $this->realm = $realm !== null ? $realm : $_SERVER['SERVER_NAME'];
    }

    /**
     * Send/set the HTTP Auth header diget
     *
     * @return void
     */
    public function send()
    {
        header('WWW-Authenticate: Basic '.
            'realm="'.$this->realm.'"'
        );

        // TODO: review
        header('HTTP/1.0 401 Unauthorized');
        // header('HTTP/1.1 401 Unauthorized');
        // echo 'HTTP Digest Authentication required for "' . $this->realm . '"';
        // exit(0);
    }

    /**
     * Authenticate the user and return username on success.
     *
     * @link    http://uk3.php.net/manual/en/features.http-auth.php
     *
     * @return mixed Either the username of the user making the request or we
     *               return access to $this->send() which will pop up the authentication
     *               challenge once again.
     */
    public function authenticate()
    {
        if (
            isset($_SERVER['PHP_AUTH_USER'])
        ) {
            $user = array('username'=>$_SERVER['PHP_AUTH_USER'], 'password'=>$_SERVER['PHP_AUTH_PW']);
            if ($this->getToken($user) === true) {
                return $_SERVER['PHP_AUTH_USER'];
            }
        }

        return $this->send();
    }

}
