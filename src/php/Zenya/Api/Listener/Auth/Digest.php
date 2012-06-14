<?php
namespace Zenya\Api\Listener\Auth;

/*
Example usage

$HTTPDigest =& new HTTPDigest();
if (
        $username = $HTTPDigest->authenticate(
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
class Digest implements Adapter
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

        $this->salt = 'pass';
        #$_SERVER['X_FRAPI_AUTH_USER'] = $this->digest['username'];
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

    /** TORM? Get the HTTP Auth header
     * @return str
     */
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

    /**
     * Authenticate the user and return username on success.
     *
     * @return str
     */
    function authenticate()
    {
        if (!isset($_SERVER['PHP_AUTH_DIGEST'])) {
            return $this->send();
        }

        if ($this->parseDigest($_SERVER['PHP_AUTH_DIGEST'])) {

            #echo '<pre>';
            #print_r($_SERVER['PHP_AUTH_DIGEST']);
            #print_r($this->digest);exit;

            #$users = Frapi_Model_Partner::isPartnerHandle( $this->digest['username'] );
            $users = array('username'=>'franck');

            if ($users === false) {
                return $this->send();
            }

            return $this->validateResponse();
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

        $ip = $remoteAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
              ? $_SERVER['HTTP_X_FORWARDED_FOR']
              : $_SERVER['REMOTE_ADDR'];

        return md5(date('Y-m-d H:i', $time) . ':' . $ip . ':' . $this->privateKey);
    }

    /**
     * Authorize the request
     *
     * This method is used to authorize the request. It fetches the
     * digest information from the request, decomposes it and finds out
     * the relevant information for authenticating the users.
     *
     * This method also makes use of Frapi_Model_Partner::isPartnerHandle()
     * to validate whether or not a user is a real user. If not then we bail
     * early.
     *
     * @link    http://www.peej.co.uk/projects/phphttpdigest.html
     * @link    http://www.faqs.org/rfcs/rfc2617.html
     *
     * @return mixed Either the username of the user making the request or we
     *               return access to $this->send() which will pop up the authentication
     *               challenge once again.
     */

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

    protected function validateResponse()
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

            echo "{$this->digest['username']}:{$this->realm}:{$this->salt}";

            $passphrase = hash('md5', "{$this->digest['username']}:{$this->realm}:{$this->salt}");

            if ($this->passwordsHashed) {
                $a1 = $passphrase;
            } else {
                $a1 = md5($this->digest['username'] . ':' . $this->realm . ':' . $passphrase);
            }
echo $a1;

            $expectedResp = $a1 . ':' . $this->digest['nonce'] . ':';
            if (isset($this->digest['qop'])) {
                $expectedResp .= $this->digest['qop'] . ':';
            }
            $expectedResp .= md5($_SERVER['REQUEST_METHOD'] . ':' . $uri);

            if ($this->digest['response'] == md5($expectedResp)) {
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