<?php

namespace Zenya\Api;

class Request
{

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

    /**
     * Constructor
     * return void
     */
    public function __construct()
    {
        #$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        #print_r($request->get());
        #exit;

        $this->setUri();
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

    public function setUri($uri = null)
    {
        if (null === $uri) {
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
        }
        $uri = parse_url($uri, PHP_URL_PATH);

        if ($uri == '') {
            $uri = '/';
        }

        if (
            $uri != '/'
            && substr($uri, -1) == '/'
        ) {
            $uri = substr($uri, 0, -1);
        }

        $this->uri = $uri;
    }

    /**
     * Sets a header by name.
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function setHeaders($headers = null)
    {
        if (null === $headers) {
            #$params = http_get_request_headers();
            $params = $_SERVER;
        } elseif (!is_array($headers)) {
            throw new \InvalidArgumentException(sprintf("%s expects an array", __METHOD__));
        }
        $this->headers = $params;
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
     * Sets all parameters.
     *
     * @param  array $params
     * @return array
     */
    public function setParams(array $params = null)
    {
        if (null === $params) {
            $params = $_REQUEST;
        } elseif (!is_array($params)) {
            throw new \InvalidArgumentException(sprintf("%s expects an array",__METHOD__ ));
        }
        $this->params = $params;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getMethod()
    {
        if (null === $this->method) {
            $this->setMethod();
        }

        return $this->method;
    }

    public function setMethod($method = null, $default='GET')
    {
        if (null === $method) {
            if ($this->getHeader('X-HTTP-Method-Override')) {
                $method = $this->getHeader('X-HTTP-Method-Override');
            } elseif ($this->getParam('method')) {
                $method = $this->getParam('method');
            } else {
                $method = isset($_SERVER['REQUEST_METHOD'])
                    ? $_SERVER['REQUEST_METHOD']
                    : $default;
            }
        }
        $this->method = strtoupper($method);
    }

    public function setBody($body = null)
    {
        if (null === $body) {
            #$body = http_get_request_body();
            $body = @file_get_contents('php://input');
        }
        $this->body = $body;
    }

    /*
     * alnum, alpha, digit
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

    public function hasHeader($key)
    {
        return isset($this->headers[$key]);
    }

    public function getHeader($key)
    {
        if (isset($this->headers[$key])) {
            return $this->headers[$key];
        }
    }

    public function getRawBody()
    {
        return $this->body;
    }

    public function getBody()
    {
        static $decodedBody = null;
        if (!is_null($decodedBody)) {
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

    public function getIP()
    {
        $ip = $this->getHeader('HTTP_CLIENT_IP');
        if (empty($ip)) {
            $ip = $this->getHeader('HTTP_X_FORWARDED_FOR');
        }

        return empty($ip) ? $this->getHeader('REMOTE_ADDR') : $ip;
    }

}
