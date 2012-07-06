<?php

namespace Zenya\Api;

class Response
{

    /**
     * List of supported formats.
     * @var array
     */
    protected $formats = array('json', 'xml', 'html');

    /**
     * Holds the current output format.
     * Also use to set the default value.
     * @var string
     */
    protected $format = 'html';

    /**
     * Character encoding.
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Holds the arrays of HTTP headers
     * @var  array
     */
    protected $headers = array();

    /**
     * Holds the current HTTP Code
     * @var  string
     */
    protected $http_code = 200;

    /**
     * Associative array of HTTP phrases.
     *
     * @var  array
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @link http://tools.ietf.org/html/rfc2616#section-10
     */
    protected static $http_phrases = array(

        // 1xx: Informational - Request received, continuing process
        100 => 'Continue',
        101 => 'Switching Protocols',

        // 2xx: Success - The action was successfully received, understood and
        // accepted
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',

        // 3xx: Redirection - Further action must be taken in order to complete
        // the request
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',

        // 4xx: Client Error - The request contains bad syntax or cannot be
        // fulfilled
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',

        // 5xx: Server Error - The server failed to fulfill an apparently
        // valid request
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'

    );

    /**
     * Associative array of long HTTP phrases.
     *
     * @var  array
     */
    protected static $long_http_phrases = array(

        200 => 'The request has succeeded.',
        201 => 'The request has been fulfilled and resulted in a new resource being created.',

        // Resulting from a POST, requires to use ->setHeader("Location", "/resource/action/id")
        202 => 'The request has been accepted for processing, but the processing has not been completed.',

        // DELETE
        204 => 'Request fulfilled successfully.',

        // Errors
        400 => 'Request is malformed.',
        401 => 'Not Authenticated.',
        403 => 'Access to this ressource has been denied.',
        404 => 'No ressource found at the Request-URI.',
        503 => 'The service is currently unable to handle the request due to a temporary overloading or maintenance of the server. Try again later.',

    );

    /**
     * @var Zenya\Api\Request
     */
    protected $request;

    public function __construct(Request $request, $sign=false, $debug=false)
    {
        $this->request = $request;

        $this->sign = $sign;
        $this->debug = $debug;
    }

    /**
     * Sets the output format.
     *
     * @param  string                    $format
     * @throws \DomainException 406
     */
    public function setFormat($format, $default)
    {
        $format = is_null($format) ? $default : $format;
        if (!in_array(strtolower($format), $this->getFormats())) {
            $this->format = strtolower($default);
            throw new \DomainException("Format ({$format}) not supported.", 406); // maybe 404?
        }
        $this->format = strtolower($format);
    }

    /**
     * Returns all the response formats available.
     *
     * @return array
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets all the response formats available.
     *
     * @return void
     */
    public function setFormats(array $formats)
    {
        $this->formats = $formats;
    }

    /**
     * Sets a header.
     *
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value, $replace=true)
    {
        if (!$replace && isset($this->headers[$key])) {
            return;
        }
        $this->headers[$key] = $value;
    }

    /**
     * Gets a specified header.
     *
     * @param  string $key
     * @return string
     */
    public function getHeader($key)
    {
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * Returns the header array.
     *
     * @param string $key
     * @param string $value
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Sends the headers thru HTTP.
     *
     * header('Cache-Control: no-cache, must-revalidate');    // HTTP/1.1
     * header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');  // Date in the past
     * // upload example
     * header('Content-Disposition: attachment; filename="downloaded.pdf"');
     * readfile('original.pdf');
     *
     * @param integer $http_code
     * @param string  $version_string
     */
    public function sendAllHttpHeaders($http_code, $version_string)
    {
        $out = array( $this->sendHeader('X-Powered-By: ' . $version_string, true, $http_code) );

        foreach ($this->headers as $key => $value) {
           $out[] = $this->sendheader($key . ': ' . $value);
        }

        return $out;
    }

    /**
     * Sends one header thru HTTP
     * @param vary
     */
    public function sendHeader()
    {
        $args = func_get_args();

        return isset($this->unit_test)
            ? $args
            : call_user_func_array('header', $args);
    }

    /**
     * Returns all the response formats available.
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * Sets the current HTTP code.
     *
     * @param  integer $int
     * @return void
     */
    public function setHttpCode($int)
    {
        $this->http_code = (int) $int;
    }

    /**
     * Gets the current HTTP code.
     *
     * @return intger
     */
    public function getHttpCode()
    {
        return $this->http_code;
    }

    /**
     * Returns an HTTP status phrase.
     *
     * @param  integer $http_code
     * @param  bolean  $long
     * @return string
     */
    public function getStatusPrases($http_code=null, $long=false)
    {
        $http_code = is_null($http_code) ? $this->http_code : $http_code;
        $type = $long === true ? self::$long_http_phrases : self::$http_phrases;
        $status =  self::$http_phrases[$http_code];

        return $long === true
            ? isset($type[$http_code]) ? $type[$http_code] : $http_code . ' ' . $status
            : $status;
    }

    /**
     * Returns sucessful or failed string.
     *
     * @param  integer $http_code
     * @return string
     */
    public function getStatusAdjective($http_code = null)
    {
        $http_code = is_null($http_code) ? $this->http_code : $http_code;

        return floor($http_code/100)<=3 ? 'successful' : 'failed';
    }

    /**
     * Returns an array representation of the output
     *
     * @return array
     */
    public function collate($name, $results)
    {
        $array = array($name => $results);

        if ($this->sign === true) {
            $array['signature'] = array(
                'request'   => sprintf('%s %s', $this->request->getMethod(), $this->request->getUri()),
                'timestamp' => gmdate('Y-m-d H:i:s') . ' UTC',
                'status'    => sprintf(
                                '%d %s - %s',
                                $this->getHttpCode(),
                                $this->getStatusPrases(),
                                $this->getStatusAdjective()
                            ),
                'client_ip' => $this->request->getIp(true)
            );
        }

        if ($this->debug === true) {
            $array['debug'] = array(
                    'headers'	=> $this->getHeaders(),
                    'format'    => $this->getFormat(),
                    #'params'    => $this->request->route->getParams(),
            );
        }

        return $array;
    }

    /**
     * Generates the response & send the headers...
     *
     * @param  array  $results
     * @param  string $version_string
     * @param  string $rootNode
     * @return string
     */
    public function generate($name, array $results, $version_string='ouarz', $rootNode='root')
    {
        $renderer = __NAMESPACE__ . '\Output\\' . ucfirst($this->getFormat());
        $output = new $renderer($this->encoding);
        $this->setHeader('Content-Type', $output->getContentType());
        $this->sendAllHttpHeaders($this->getHttpCode(), $version_string);

        return $output->encode($this->collate($name, $results), $rootNode);
    }

}
