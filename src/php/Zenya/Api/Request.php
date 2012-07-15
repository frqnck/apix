<?php

namespace Zenya\Api;

class Request
{

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
     * Hold the request body (raw)
     * @var string
     */
    protected $body = null;

    /**
     * Holds the HTTP method
     * @var string
     */
    protected $method = null;

    // /**
    //  * TEMP: The singleton instance
    //  * @var Request
    //  */
    // private static $instance = null;

    // /**
    //  * TEMP: Returns as a singleton instance
    //  *
    //  * @return Request
    //  */
    // public static function getInstance()
    // {
    //     if (null === self::$instance) {
    //         self::$instance = new self;
    //     }

    //     return self::$instance;
    // }

    // /**
    //  * TEMP: disalow cloning.
    //  *
    //  * @codeCoverageIgnore
    //  */
    // private final function __clone() {}

    /**
     * Constructor
     * return void
     */
    public function __construct()
    {
        #$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        #print_r($request->get());
        #exit;

        $this->setHeaders();
        $this->setParams();
        $this->setBody();
    }

    public function getUri()
    {
        if (null === $this->uri) {
            $this->setUri();
        }

        return $this->uri;
    }

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
     * Sets a parameter by name.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function setParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    /**
     * Gets a pspecified param.
     *
     * @param  string $key
     * @param  string $filter POSIX character classes e.g. alnum, alpha, digit.
     * @return mixed
     */
    public function getParam($key, $filter=null)
    {
        if (isset($this->params[$key])) {
            if (null !== $filter) {
                return preg_replace('/[^[:' . $filter . ':]]/', '', $this->params[$key]);
            }

            return $this->params[$key];
        }
    }

    /**
     * Sets all parameters.
     *
     * @param  array $params
     * @return array
     */
    public function setParams(array $params = null)
    {
        if (null === $params) {
            $params = $_REQUEST;
        }
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setMethod($method = null, $default='GET')
    {
        if (null === $method) {
            if ($this->hasHeader('X-HTTP-Method-Override')) {
                $method = $this->getHeader('X-HTTP-Method-Override');
            } elseif ($this->getParam('_method')) {
                $method = $this->getParam('_method');
            } else {
                $method = isset($_SERVER['REQUEST_METHOD'])
                    ? $_SERVER['REQUEST_METHOD']
                    : $default;
            }
        }
        $this->method = strtoupper($method);
    }

    public function getMethod()
    {
        if (null === $this->method) {
            $this->setMethod();
        }

        return $this->method;
    }

    /**
     * Sets a header by name
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

   /**
     * Checks if specified header exist
     *
     * @param  string $key The key
     * @return bolean
     */
    public function hasHeader($key)
    {
        return isset($this->headers[$key]);
    }

    /**
     * Returns the specified header
     *
     * @param  string $key The key
     * @return mixed
     */
    public function getHeader($key)
    {
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
    }

    /**
     * Populates the header array.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function setHeaders(array $headers = null)
    {
        if (null === $headers) {
            #$headers = http_get_request_headers();
            $headers = $_SERVER;
        }
        $this->headers = $headers;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getIp()
    {
        $ip = $this->getHeader('HTTP_CLIENT_IP');
        if (empty($ip)) {
            $ip = $this->getHeader('HTTP_X_FORWARDED_FOR');
        }

        return empty($ip) ? $this->getHeader('REMOTE_ADDR') : $ip;
    }

    protected $bodyStream = 'php://input';

    public function setBodyStream($string)
    {
        $this->bodyStream = $string;
    }

    public function setBody($body = null)
    {
        if (null === $body) {
            $body = file_get_contents($this->bodyStream);
        }
        $this->body = $body;
    }

    public function hasBody()
    {
        return !empty($this->body);
    }

    public function getRawBody()
    {
        return $this->body;
        #return = http_get_request_body();
        #return file_get_contents($this->bodyStream);
    }

    public function getBody($cache=true)
    {
        static $decodedBody = null;
        if ($cache && null !== $decodedBody) {
            return $decodedBody;
        }

        // Decode any content-encoding (gzip or deflate) if needed
        switch (strtolower($this->getHeader('content-encoding'))) {

            // Handle gzip encoding
            case 'gzip':
                require_once 'HTTP/Request2.php';
                $decodedBody = \HTTP_Request2_Response::decodeGzip($this->body);
                break;

            // Handle deflate encoding
            case 'deflate':
                require_once 'HTTP/Request2.php';
                $decodedBody = \HTTP_Request2_Response::decodeDeflate($this->body);
                break;

            default:
                return $this->body;
        }

        return $decodedBody;
    }

}
