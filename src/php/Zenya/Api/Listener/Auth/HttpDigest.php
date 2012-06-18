<?php
namespace Zenya\Api\Listener\Auth;

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
class HttpDigest implements Adapter
{

    /**
     * @var string The opaque value
     */
    var $opaque = 'opaque';

    /**
     * @var string The authentication realm.
     */
    var $realm = null;

    /**
     * @var string The base URL of the application.
     */
    var $baseURL = '/';

    /**
     * @var boolean Are passwords stored as an a1 hash (username:realm:password) rather than plain text.
     */
    var $passwordsHashed = true;

    /**
     * @var string  The private key.
     */
    var $privateKey = 'privatekey';

    /**
     * @var int The life of the nonce value in seconds
     */
    var $nonceLife = 300;

    /**
     * @var string The token to macth with the digest password
     */
    public $token = null;

    /**
     * Constructor
     *
     * The constructor that sets the $this->realm
     *
     * @param string $realm Perhaps a custom realm. Default is null so the
     *                      realm will be $_SERVER['SERVER_NAME']
     */
    public function __construct($realm = null, $privateKey='privateSaltedKey', $opaque='opaque')
    {
        $this->realm = $realm !== null ? $realm : $_SERVER['SERVER_NAME'];

        $this->privateKey = $privateKey;
        $this->opaque = $opaque;
    }

    /**
     * Returns the token
     *
     * @param array Teh digest array
     * @return string The token to macth with the digest password
     */
    function getToken(array $digest)
    {
      if(is_null($this->token)) {
        call_user_func_array($this->setToken, array($digest));
      }
      return $this->token;
    }

    /**
     * Send/set the HTTP Auth header diget
     *
     * @return void
     */
    function send()
    {
        header('WWW-Authenticate: Digest '.
            'realm="'.$this->realm.'", '.
            'domain="'.$this->baseURL.'", '.
            'qop=auth, '.
            'algorithm=MD5, '.
            'nonce="'.$this->getNonce().'", '.
            'opaque="'.$this->getOpaque().'"'
        );

        // TODO: review
        header('HTTP/1.0 401 Unauthorized');
        // header('HTTP/1.1 401 Unauthorized');
        // echo 'HTTP Digest Authentication required for "' . $this->realm . '"';
        // exit(0);
    }

    /** TODO: RM? Get the HTTP Auth header
     * @return str
     */
/*
    function getAuthHeader()
    {
        if (isset($_SERVER['Authorization'])) {
            return $_SERVER['Authorization'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }
        return NULL;
    }
*/
    /**
     * Authenticate the user and return username on success.
     *
     * @link    http://www.peej.co.uk/projects/phphttpdigest.html
     * @link    http://www.faqs.org/rfcs/rfc2617.html
     *
     * @return mixed Either the username of the user making the request or we
     *               return access to $this->send() which will pop up the authentication
     *               challenge once again.
     */
    function authenticate()
    {
        if (
            isset($_SERVER['PHP_AUTH_DIGEST'])
            && $this->parseDigest($_SERVER['PHP_AUTH_DIGEST'])
        ) {
            $token = $this->getToken($this->digest);
            if (!isset($token)) {
                return $this->send();
            }

            return $this->validate($token);
        }

        return $this->send();
    }

    /**
     * Gets the nonce value for HTTP Digest.
     *
     * @return string
     */
    function getNonce()
    {
        $time = ceil(time() / $this->nonceLife) * $this->nonceLife;

        $remoteAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
              ? $_SERVER['HTTP_X_FORWARDED_FOR']
              : $_SERVER['REMOTE_ADDR'];

        return md5(date('Y-m-d H:i', $time) . ':' . $remoteAddress . ':' . $this->privateKey);
    }

    protected function parseDigest($digest)
    {
        // username="test", realm="test.dev", nonce="e8ae165a8fa2a10bb09303012556952c", uri="/", response="dcfe5fb7a2e3160155dc46f5eb590035", opaque="94619f8a70068b2591c2eed622525b0e", algorithm="MD5", cnonce="f976912c5322bfc760a13155b254d5b3", nc=00000001, qop="auth"
        /*
        echo $digest;
        preg_match('/username="([^"]+)", realm="([^"]+)", nonce="([^"]+)"/', $digest, $m);
        echo '<hr>';
        echo "<pre>";
        print_r($m);
        exit;
        */

        if (preg_match('/username="([^"]+)"/', $digest, $username)
            && preg_match('/[,| ]nonce="([^"]+)"/', $digest, $nonce)
            && preg_match('/response="([^"]+)"/', $digest, $response)
            && preg_match('/opaque="([^"]+)"/', $digest, $opaque)
            && preg_match('/uri="([^"]+)"/', $digest, $uri))
        {
            $this->digest = array_map(
                function($a){return array_pop($a);},
                compact('username', 'nonce', 'response', 'opaque', 'uri')
            );

            // check for quality of protection
            if (
                preg_match('/qop="?([^,\s"]+)/', $digest, $qop)
                && preg_match('/nc=([^,\s"]+)/', $digest, $nc)
                && preg_match('/cnonce="([^"]+)"/', $digest, $cnonce)
            ) {
                $this->digest['qop'] = $nc[1] . ':' . $cnonce[1] . ':' . $qop[1];
            }

            return true;
        }

        return false;
    }

    protected function validate($token)
    {
        $uri = $_SERVER['REQUEST_URI'];

        // hack for IE which does not pass querystring in URI element of Digest string or in response hash
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strlen($this->digest['uri']));
        }

        if (
            $this->getOpaque() == $this->digest['opaque']
            && $uri == $this->digest['uri']
            && $this->getNonce() == $this->digest['nonce']
        ) {
            $passphrase = hash('md5', "{$this->digest['username']}:{$this->realm}:{$token}");

            if ($this->passwordsHashed) {
                $a1 = $passphrase;
            } else {
                // hum??
                $a1 = md5($this->digest['username'] . ':' . $this->realm . ':' . $passphrase);
            }

            $expected = $a1 . ':' . $this->digest['nonce'] . ':';
            if (isset($this->digest['qop'])) {
                $expected .= $this->digest['qop'] . ':';
            }
            $expected .= md5($_SERVER['REQUEST_METHOD'] . ':' . $uri);

            if ($this->digest['response'] == md5($expected)) {
                return $this->digest['username'];
            }
        }

        return $this->send();
    }

    /**
     * Gets opaque value for HTTP Digest.
     *
     * @return string
     */
    function getOpaque()
    {
        return md5($this->opaque);
    }

}