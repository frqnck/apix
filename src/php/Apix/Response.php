<?php

namespace Apix;

/**
 * Represents a response.
 */
class Response extends Listener
{

    /**
     * List of supported response formats.
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
     * Holds the arrays of HTTP headers.
     * @var  array
     */
    protected $headers = array();

    /**
     * Holds the current HTTP Code.
     * @var  string
     */
    protected $http_code = 200;

    /**
     * Holds the current output.
     * @var  string
     */
    public $output = null;

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
        501 => 'This resource entity is not (yet) implemented. Try again later.',
        503 => 'The service is currently unable to handle the request due to a temporary overloading or maintenance of the server. Try again later.'
    );

    /**
     * @var Apix\Request
     */
    protected $request;

   /**
     * Returns the request object.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @var Apix\Router
     */
    protected $route;

    /**
     * Sets the route object.
     */
    public function setRoute(Router $route)
    {
        $this->route = $route;
    }

    /**
     * Returns the route object.
     *
     * @return Router
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Sets the output format.
     *
     * @param  string           $format
     * @param  string           $default
     * @throws \DomainException 406
     */
    public function setFormat($format, $default=null)
    {
        $format = strtolower($format);
        if (!in_array($format, $this->getFormats())) {
            $this->format = strtolower($default);
            throw new \DomainException(
                sprintf('Format (%s) not supported.', $format),
                406 // maybe 404?
            );
        }

        $this->format = $format;
    }

    /**
     * Returns the output format.
     *
     * @return string
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
     * Returns all the response formats available.
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * Sets a response header.
     *
     * @param string  $key
     * @param string  $value
     * @param boolean $overwrite Wether to overwrite an existing header.
     */
    public function setHeader($key, $value, $overwrite=true)
    {
        if (!$overwrite && isset($this->headers[$key])) {
            return;
        }
        $this->headers[$key] = $value;
    }

    /**
     * Gets the specified response header.
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
     * @return array
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
        // PHP bug? TODO:
        // $out = $this->sendheader("Status: $http_code " . static::getStatusPrases($http_code));
        // //$out = $this->sendheader("HTTP/1.0 $http_code " . static::getStatusPrases($http_code), true);
        // $out[] = array( $this->sendHeader('X-Powered-By: ' . $version_string) );

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
     * @param  boolean $long
     * @return string
     */
    public static function getStatusPrases($http_code=null, $long=false)
    {
        //$http_code = is_null($http_code) ? $this->http_code : $http_code;
        $type = $long === true ? self::$long_http_phrases : self::$http_phrases;
        $status = self::$http_phrases[$http_code];

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
    public static function getStatusAdjective($http_code)
    {
        return floor($http_code/100)<=3 ? 'successful' : 'failed';
    }

    /**
     * Returns a collated array representation of the output.
     *
     * @return array
     */
    public function collate(array $results)
    {
        $top = $this->route->getController() ?: 'index';
        return array($top => $results);
    }

    /**
     * Generates the response & send the headers...
     *
     * @param  array  $results
     * @param  string $version_string
     * @param  string $rootNode
     * @return string
     */
    public function generate(array $results, $version_string='ouarz', $rootNode='root')
    {
        $this->results = $this->collate($results);

        // early listeners @ post-response
        $this->hook('response', 'early');

        $renderer = __NAMESPACE__ . '\Output\\' . ucfirst($this->getFormat());
        $view = new $renderer($this->encoding);
        $this->setHeader('Content-Type', $view->getContentType());
        $this->sendAllHttpHeaders($this->getHttpCode(), $version_string);

        if (null === $this->output) {
            $this->output = $view->encode(
                $this->results,
                $rootNode
            );
        }

        // late listeners @ post-response
        $this->hook('response', 'late');
    }

    /**
     * Returns the response output.
     *
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Sets the response output.
     *
     * @param string $string
     */
    public function setOutput($string)
    {
        $this->output = $string;
    }

}
