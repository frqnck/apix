<?php
/**
 * Authorization Digest Challenge + Logic
 *
 * http://php.net/manual/en/features.http-auth.php
 */
class Authorization_HTTP_Digest extends Authorization
{
    /**
     * The secret key
     *
     * @var     string The secret key
     */
    public $secretKey = 'secretKey--&@72';

    /**
     * This variable contains the parsed digest data
     */
    protected $digest = null;


    /**
     * Use a custom secret key.
     *
     * This method is used to modify the secretKey salt value of
     * the Auth object.
     *
     * @param string $secretKey The new secret key to use when salting
     *                           the authentication challenge.
     * @return void
     */
    public function setSecretKey($secretKey)
    {
         $this->secretKey = $secretKey;
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
     * @link   http://www.peej.co.uk/projects/phphttpdigest.html
     *
     * @return mixed Either the username of the user making the request or we
     *               return access to $this->send() which will pop up the authentication
     *               challenge once again.
     */
    public function authorize()
    {
        if (!isset($_SERVER['PHP_AUTH_DIGEST'])) {
            return $this->send();
        }

        if ($this->_parseDigest($_SERVER['PHP_AUTH_DIGEST'])) {

            #print_r($_SERVER['PHP_AUTH_DIGEST']);
            #print_r($this->digest);exit;

            #$users = Frapi_Model_Partner::isPartnerHandle($this->digest['username']);
            $users = array('api_key'=>'test');

            if ($users === false) {
                return $this->send();
            }

            return $this->_validateResponse($users['api_key']);
        }

        return $this->send();
    }

    protected function _parseDigest($digest)
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
            $this->digest = compact('username', 'nonce', 'response', 'opaque', 'uri');
            $this->digest['username'] = $this->digest['username'][1];

            return true;
        }

        return false;
    }

    protected function _validateResponse($api_key)
    {
        echo $requestURI = $_SERVER['REQUEST_URI'];
        #$_SERVER['X_FRAPI_AUTH_USER'] = $this->digest['username'];

        // hack for IE which does not pass querystring in URI element of Digest string or in response hash
        if (strpos($requestURI, '?') !== false) {
            $requestURI = substr($requestURI, 0, strlen($this->digest['uri'][1]));
        }

        if (
            $this->getOpaque() == $this->digest['opaque'][1]
            && $requestURI == $this->digest['uri'][1]
            && $this->getNonce() == $this->digest['nonce'][1]
        ) {

            $passphrase = hash('md5', "{$this->digest['username']}:{$this->realm}:{$api_key}");

            if ($this->passwordsHashed) {
                $a1 = $passphrase;
            } else {
                $a1 = md5($this->digest['username'] . ':' . $this->realm . ':' . $passphrase);
            }

            $expectedResp = $a1 . ':' . $this->digest['nonce'][1] . ':';
            if (
                preg_match('/qop="?([^,\s"]+)/', $_SERVER['PHP_AUTH_DIGEST'], $qop)
                && preg_match('/nc=([^,\s"]+)/', $_SERVER['PHP_AUTH_DIGEST'], $nc)
                && preg_match('/cnonce="([^"]+)"/', $_SERVER['PHP_AUTH_DIGEST'], $cnonce)
            ) {
                $expectedResp .= $nc[1] . ':' . $cnonce[1] . ':' . $qop[1];
            }
            $a2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $requestURI);
            $expectedResp .= ':' . $a2;

            if ($this->digest['response'][1] == md5($expectedResp)) {
                return $this->digest['username'];
            }
        }

        return $this->send();
    }



}



////////////////////////////////////////////

    // If this is a public action, it doesn't need authorization.
    #if (Frapi_Rules::isPublicAction($this->getAction())) {
    #	return true;
    #}

    //For Basic HTTP Auth, use headers automatically filled by PHP, if available.

    $auth_params = array(
        'digest' => isset($_SERVER['PHP_AUTH_DIGEST']) ? $_SERVER['PHP_AUTH_DIGEST'] : null
    );

    // First step: Set the state of the context objects.
#	$partner =
#		$this->authorization
#			 ->getPartner()
#			 ->setAction($this->getAction())
#			 ->setAuthorizationParams($auth_params);

    /**
     * Second step: Run the authorization, error in case of
     * error in returned values, else it's just a true.
     */
    #$partnerAuth = $partner->authorize();
$auth = new Authorization_HTTP_Digest();

/**
 * Make sure the params needed are passed
 * if not, return an error with invalid partner
 * id/key
 */
#if (!empty($_SERVER['digest'])) {
$authed = $auth->authorize();

if (isset($_GET['clean'])) {
    $_SERVER['PHP_AUTH_DIGEST'] = null;
    $auth->send();
}

if ($authed) {
    echo "<pre>";
    print_r($_SERVER);
}
