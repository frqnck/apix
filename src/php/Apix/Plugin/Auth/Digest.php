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
 * HTTP Digest authentication.
 *
 * Adapted from Paul James's implemenation.
 * @link http://www.peej.co.uk/files/httpdigest.phps
 *
 * @author Franck Cassedanne
 * @codeCoverageIgnore
 */
class Digest extends AbstractAuth
{

    /**
     * Holds the salt (private key).
     * @var string.
     */
    protected $salt = null;

    /**
     * Holds the opaque value.
     * @var string
     */
    protected $opaque = null;

    /**
     * Enable a1 hashing (username:realm:password) or use plain text.
     * @var boolean
     */
    protected $a1_hashing = true;

    /**
     * @var int The life of the nonce value in seconds
     */
    protected $nonce_life = 300;

    /**
     * Constructor
     * @param string $realm Perhaps a custom realm. Default is null so the
     *                      realm will be $_SERVER['SERVER_NAME']
     */
    public function __construct($realm = null, $salt='Peppered and Salted', $opaque='opaque')
    {
        $this->realm = null !== $realm
                     ? $realm
                     : $_SERVER['SERVER_NAME'];

        $this->salt = $salt;
        $this->opaque = md5($opaque);
    }

    /**
     * @{@inheritdoc}
     */
    public function send()
    {
        $digest = sprintf(
            'Digest realm="%s", domain="%s", qop=auth, algorithm=MD5,'
            . ' nonce="%s", opaque="%s"',
            $this->realm, $this->base_url,
            $this->getNonce(), $this->opaque
        );

        header('WWW-Authenticate: ' . $digest);
        header('HTTP/1.0 401 Unauthorized');
    }

    /**
     * @{@inheritdoc}
     *
     * @link    http://www.peej.co.uk/projects/phphttpdigest.html
     * @link    http://www.faqs.org/rfcs/rfc2617.html
     */
    public function authenticate()
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
    public function getNonce()
    {
        $time = ceil(time() / $this->nonce_life) * $this->nonce_life;

        $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
              ? $_SERVER['HTTP_X_FORWARDED_FOR']
              : $_SERVER['REMOTE_ADDR'];

        return md5(date('Y-m-d H:i', $time) . ':' . $ip . ':' . $this->salt);
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
                function ($a) {return array_pop($a);},
                compact('username', 'nonce', 'response', 'opaque', 'uri')
            );

            $this->username = $this->digest['username'];

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

        // IE hack (remove querystring from response hash)
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strlen($this->digest['uri']));
        }

        if (
            $this->opaque == $this->digest['opaque']
            && $uri == $this->digest['uri']
            && $this->getNonce() == $this->digest['nonce']
        ) {
            $passphrase = hash(
                'md5',
                "{$this->digest['username']}:{$this->realm}:{$token}"
            );

            $pass = $this->a1_hashing
                    ? $passphrase
                    : md5(
                            $this->digest['username']
                            . ':' . $this->realm
                            . ':' . $passphrase
                        );

            $expected = $pass . ':' . $this->digest['nonce'] . ':';
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

    /** TODO: RM? Get the HTTP Auth header
     * @return str
     */
/*
    public function getAuthHeader()
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

}
