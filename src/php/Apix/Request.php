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

namespace Apix;

class Request
{
    const METHOD_OVERRIDE = '_method';

    /**
     * Holds the URI string
     * @var string
     */
    protected $uri = null;

    /**
     * The HTTP response headers array
     * @var array
     */
    protected $headers = array();

    /**
     * Hold the request body (raw) data
     * @var string
     */
    protected $body = null;

    /**
     * Holds the HTTP method
     * @var string
     */
    protected $method = null;

    /**
     * Body stream scheme
     * @var string
     */
    protected $bodyStream = 'php://input';

    /**
     * Constructor, at instanciation sets the minimum request properties
     */
    public function __construct()
    {
        $this->setHeaders();
        $this->setParams();
        $this->setBody();
    }

    /**
     * Sets and parse the provided URI, or if missing guess from the enviroment
     *
     * @param string|false $uri If false, extract one from $_SERVER variables
     */
    public function setUri($uri=false)
    {
        $uri = false === $uri ? $this->getRequestUri() : $uri;
        $uri = parse_url($uri, PHP_URL_PATH);

        if ( $uri != '/' && substr($uri, -1) == '/' ) {
            $uri = substr($uri, 0, -1);
        }

        $this->uri = $uri;
    }

    /**
     * Extacts the request URI $_SERVER variables enviroment
     *
     * @return string
     */
    public function getRequestUri()
    {
        if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        } elseif (isset($_SERVER['IIS_WasUrlRewritten'])
                && $_SERVER['IIS_WasUrlRewritten'] == '1'
                && isset($_SERVER['UNENCODED_URL'])
                && $_SERVER['UNENCODED_URL'] != ''
        ) {
            $uri = $_SERVER['UNENCODED_URL'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        } elseif (isset($_SERVER['PATH_INFO'])) {
            $uri = $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            $uri = $_SERVER['ORIG_PATH_INFO'];
        }

        return isset($uri) ? $uri : '/';
    }

    /**
     * Gets the current URI, if undefined guess and set one up.
     *
     * @return string
     */
    public function getUri()
    {
        if (null === $this->uri) {
            $this->setUri();
        }

        return $this->uri;
    }

    /**
     * Sets a parameter by name
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Gets a specified param
     *
     * @param  string  $key
     * @param  boolean $raw    Set to true to get the raw URL encoded value
     * @param  string  $filter POSIX character classes e.g. alnum, alpha
     * @return mixed
     */
    public function getParam($key, $raw=false, $filter=null)
    {
        if (isset($this->params[$key])) {

            $param = $raw===false ? rawurldecode($this->params[$key])
                                  : $this->params[$key];

            if (null !== $filter) {
                return preg_replace('/[^[:' . $filter . ':]]/', '', $param);
            }

            return $param;
        }
    }

    /**
     * Sets all parameters
     *
     * @param array|null $params
     */
    public function setParams(array $params = null)
    {
        $this->params = null === $params ? $_REQUEST : $params;
    }

    /**
     * Returns all the request parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Sets the request method either using:
     *   - the passed method value,
     *   - or from an override value:
     *       - X-HTTP-Method-Override,
     *       - a GET param override,
     *       - server env or use the default value.
     *
     * @param string $method
     * @param string $default
     */
    public function setMethod($method = null, $default = 'GET')
    {
        if (null === $method) {
            if ($this->hasHeader('X-HTTP-Method-Override')) {
                $method = $this->getHeader('X-HTTP-Method-Override');
            } elseif ($this->getParam(self::METHOD_OVERRIDE)) {
                $method = $this->getParam(self::METHOD_OVERRIDE);
            } else {
                $method = isset($_SERVER['REQUEST_METHOD'])
                    ? $_SERVER['REQUEST_METHOD']
                    : $default;
            }
        }
        $this->method = strtoupper($method);
    }

    /**
     * Returns the current requet method
     *
     * @param string
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->setMethod();
        }

        return $this->method;
    }

    /**
     * Sets a request header by name
     *
     * @param string $name
     * @param string $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[strtoupper($name)] = $value;
    }

    /**
     * Checks if specified header exists
     *
     * @param  string  $name
     * @return boolean
     */
    public function hasHeader($name)
    {
        return isset($this->headers[strtoupper($name)]);
    }

    /**
     * Returns the specified header
     *
     * @param  string $name
     * @return string
     */
    public function getHeader($name)
    {
        $name = strtoupper($name);
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }
    }

    /**
     * Populates the headers properties
     * Will use the provided associative array or extract things from $_SERVER
     *
     * @param array $headers The value
     */
    public function setHeaders(array $headers = null)
    {
        if (null === $headers) {
            #$headers = http_get_request_headers();
            $headers = $_SERVER;
        }
        $this->headers = $headers;
        // $this->headers = array_change_key_case($headers);
    }

    /**
     * Returns all the headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Returns the client's IP address
     *
     * @return string
     */
    public function getIp()
    {
        $ip = $this->getHeader('HTTP_CLIENT_IP');
        if (empty($ip)) {
            $ip = $this->getHeader('HTTP_X_FORWARDED_FOR');
        }

        return empty($ip) ? $this->getHeader('REMOTE_ADDR') : $ip;
    }

    /**
     * Sets the body stream property
     *
     * @param string $string
     */
    public function setBodyStream($string)
    {
        $this->bodyStream = $string;
    }

    /**
     * Sets the body using the provided string or retrieve it from a PHP stream
     *
     * @param string $body
     */
    public function setBody($body = null)
    {
        $this->body = null === $body
            ? file_get_contents($this->bodyStream)
            : $body;
    }

    /**
     * Checks wether the body contains data
     *
     * @return boolean
     */
    public function hasBody()
    {
        return !empty($this->body);
    }

    /**
     * Returns the raw (undecoded) body data
     *
     * @return string
     */
     public function getRawBody()
    {
        return $this->body;
        #return = http_get_request_body();
        #return file_get_contents($this->bodyStream);
    }

    /**
     * Returns the (decoded) body data of a request
     *
     * @param  boolean                   $cache Wether to cache the body after decoding.
     * @return string
     * @throws \BadFunctionCallException
     */
    public function getBody($cache=true)
    {
        static $body = null;
        if ($cache && null !== $body) {
            return $body;
        }

        // Decode any content-encoding (gzip or deflate) if needed
        switch (strtolower($this->getHeader('content-encoding'))) {
            // Handle gzip encoding
            case 'gzip':
                $body = $this->gzDecode($this->body);
                break;

            // Handle deflate encoding
            case 'deflate':
                $body = $this->gzInflate($this->body);
                break;

            default:
                return $this->body;
        }

        return $body;
    }

    /**
     * Handles gzip decoding
     *
     * @param  boolean                   $cache Wether to cache the body after decoding.
     * @return string
     * @throws \BadFunctionCallException
     * @codeCoverageIgnore
     */
    public function gzDecode($data)
    {
        return function_exists('gzdecode')
                ? gzdecode($data)
                : file_get_contents(
                    'compress.zlib://data:;base64,'
                    . base64_encode($data)
                );
    }

    /**
     * Handles inflating a deflated string
     *
     * @param  string                    $data
     * @return string
     * @throws \BadFunctionCallException
     * @codeCoverageIgnore
     */
    public function gzInflate($data)
    {

        if (! function_exists('gzinflate')) {
            throw new \BadFunctionCallException(
                'zlib extension is required to deflate this'
            );
        }

        return gzinflate($data);
    }

}
