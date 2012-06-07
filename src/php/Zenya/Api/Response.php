<?php

/** @see Zenya_Api_Response_Interface */
#require_once 'Zenya/Api/Response/Interface.php';

namespace Zenya\Api;

class Response
{
    /**
     * Format to use by default when not provided.
     * @var string
     */
    const DEFAULT_FORMAT = 'html';

    /**
     * List of supported formats.
     * @var array
     */
    protected $formats = array('json', 'xml', 'html', 'php');

    /**
     * Holds the current output format.
     * @var string
     */
    protected $format = null;

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
    protected $httpCode = 200;

    /**
     * Associative array of HTTP phrases.
     *
     * @var  array
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @link http://tools.ietf.org/html/rfc2616#section-10
     */
    protected static $httpPhrases = array(

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
    protected static $longHttpPhrases = array(

        200 => 'The request has succeeded.',
        201 => 'The request has been fulfilled and resulted in a new resource being created.',
        
        // Resulting from a POST, requires to use ->setHeader("Location", "http://url/action/id")
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
     * @var Zenya\Api\Server
     */
    protected $server;

    public function __construct(Server $server=null)
    {
        $this->server = $server;
    }

    /**
     * Set the current format
     *
     * @param string $format
     * @throws \InvalidArgumentException    406
     */
    public function setFormat($format)
    {
        if (!in_array(strtolower($format), $this->formats)) {
            throw new \InvalidArgumentException("Format ({$format}) not supported.", 406); // TODO: maybe 404?
        }
        $this->format = strtolower($format);
    }

    /**
     * Get all the response formats available.
     *
     * @return array
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Set all the response formats available.
     *
     * @return void
     */
    public function setFormats(array $formats)
    {
        $this->formats = $formats;
    }

    /**
     * Set/store a HTTP header.
     *
     * @param string $key
     * @param string $value
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * Set/store a HTTP header.
     *
     * @param string $key
     * @param string $value
     */
    public function getHeaders()
    {
        return $this->headers;
    }


    /**
     * Set all the HTTP headers
     *
     *  #header('Cache-Control: no-cache, must-revalidate');    // HTTP/1.1
     *  #header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');  // Date in the past
     *      // upload example
     *  #header('Content-Disposition: attachment; filename="downloaded.pdf"');
     *  #readfile('original.pdf');
     * @param   integer  $httpCode
     * @param   string  $versionString
     */
    public function sendAllHttpHeaders($httpCode, $versionString)
    {
        $out = array( $this->sendHeader('X-Powered-By: ' . $versionString, true, $httpCode) );

        // iterate and send all the headers
        foreach($this->headers as $key => $value) {
           $out[] = $this->sendheader($key . ': ' . $value);
        }
        return $out;
    }

    public function sendHeader()
    {
        if(isset($this->unit_test)) {
            return func_get_args();
        }
        return call_user_func_array('header', func_get_args());
    }

    /**
     * Get all the response formats available.
     *
     * @return array
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * Set the current HTTP code.
     *
     * @param   integer     $int
     * @return void
     */
    public function setHttpCode($int)
    {
        $this->httpCode = (int) $int;
    }

    /**
     * Get the current HTTP code.
     *
     * @return intger
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Get an HTTP status phrase.
     *
     * @param   integer $httpCode
     * @param   bolean $long
     * @return  string
     */
    public function getStatusPrases($httpCode=null, $long=false)
    {
        $httpCode = is_null($httpCode) ? $this->httpCode : $httpCode;
        $type = $long === true ? self::$longHttpPhrases : self::$httpPhrases;
        $status = $httpCode . ' ' . self::$httpPhrases[$httpCode];
        return $long === true
            ? isset($type[$httpCode]) ? $type[$httpCode] : $status . ' (not implemented)'
            : $status;
    }

    /**
     * TODO: Get the an HTTP phrase (long or short).
     *
     * @param   integer $httpCode
     * @param   bolean $long
     * @return  string
     */
    public static function getStatusString($int)
    {
        static $status = null;
        if (!is_null($status)) {
            // Response status already set, something must be wrong.
            throw new \Exception('Internal Error', 500);
        }

        return floor($int/100)<=3 ? 'successful' : 'failed';
    }

    public function toArray()
    {
        $req = $this->server->request;
        $route = $this->server->route;

        $signature = true; //$this->server->signature;
        $debug = $this->server->debug;

        $array = array(
            $this->server->route->getController() => $this->server->getResults()
        );

        if ($signature == true) {
            $array['signature'] = array(
                'request'   => sprintf('%s %s', $req->getMethod(), $req->getUri()),
                'timestamp' => gmdate('Y-m-d H:i:s') . ' UTC',
                'status'    => sprintf(
                                '%d %s - %s',
                                $this->httpCode,
                                self::$httpPhrases[$this->httpCode], // todo
                                self::getStatusString($this->httpCode)
                            ),
                'client_ip'        => $req->getIp(true)
            );
        }

        if ($debug == true) {
            $array['debug'] = array(
                    'headers'	=> $this->headers,
                    'format'    => $this->format,
                    'params'    => $route->params,  // Params
            );
        }

        return $array;
    }

    /**
     * Send the response
     *
     * @param Resource $resource The Resource object
     * @param boolean
     *
     * @return string
     */
    public function send($withBody=true)
    {
        $resource = &$this->server->resource;

        $format = isset($this->format)
            ? $this->format : self::DEFAULT_FORMAT;

        $this->setFormat($format);

        // format
        $output = 'Zenya\Api\Output\\' . ucfirst($this->format);
        $output = new $output($this->encoding);

        // sset the headers entries
        $this->setHeader('Content-Type', $output->getContentType());

        $body = $output->encode($this->toArray(), $this->server->rootNode);

        $this->sendAllHttpHeaders($this->server->httpCode, $this->server->version);

        return $withBody !== false ? $body : null;
    }

}
