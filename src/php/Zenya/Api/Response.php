<?php

/** @see Zenya_Api_Response_Interface */
#require_once 'Zenya/Api/Response/Interface.php';

namespace Zenya\Api;

class Response
{
    /**
     * Format to use by default when not provided.
     */
    const DEFAULT_FORMAT = 'html';

	/**
	 * Constante used for status report.
	 */
	const SUCCESS = 'successful', FAILURE = 'failed';

	/**
     * Associative array of HTTP status code / reason phrase.
     *
     * @var  array
     * @link http://tools.ietf.org/html/rfc2616#section-10
     */
    protected static $defs = array(

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

        // 5xx: Server Error - The server failed to fulfill an apparently
        // valid request
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded',

    );

	/**
	 * Hold the output format.
	 * @var string
	 */
	public $format = null;

    /**
     * List of supported formats.
     * @var array
     */
    static public $formats = array('json', 'xml', 'html', 'php');
    
	/**
     * @var Zenya_Api_Server
     */
    protected $server;

    public function __construct(Server $server, $format)
    {
		$this->server = $server;
		$this->format = $format;
    }

	public function toArray()
	{
		$data = array();
		foreach($this->server->results as $key => $value) {
			$data[$key] = $value;
		}

		$req = $this->server->request;
		$array = array(
			'zenya' => array(
				$this->server->route->name => $data,
			),
			'signature'	=> array(
				'status'	=> self::getStatusString($this->server->httpCode) . ' (' . $this->server->httpCode . ' ' . self::$defs[$this->server->httpCode] . ')',
				'request'	=> $req->getMethod() . ' ' . $req->getUri(), # . '.' . $this->format,
				'timestamp'	=> $this->getDateTime()
			)
		);

		if ($this->server->debug == true) {
			$array['debug'] = array(
				'debug' => array(
				//	'request'	=> $req->getPathInfo(),	// Request URI
					'params'	=> $req->getParams(),	// Params
					'format'	=> $this->format,
				//	'ip'		=> $req->getClientIp(true)
					#'http'		=> $this->getHttp(),
				),
			);
		}
		return $array;
	}
	
	protected function getDateTime($time=null)
	{
		return gmdate('Y-m-d H:i:s') . ' UTC';
		#$dt = new \DateTime($time);
        #$dt->setTimezone(new \DateTimeZone('UTC'));
        #return $dt->format(\DateTime::ISO8601);
	}

	static public function getStatusString($int)
	{
		static $status = null;
		if (!is_null($status)) {
			// Response status already set, something must be wrong.
			throw new \Exception('Internal Error', 500);
		}
		return floor($int/100)<=3 ? self::SUCCESS : self::FAILURE;
	}

	
	
	public function send()
    {
		$format = isset($this->format) ? $this->format : self::DEFAULT_FORMAT;
		$formatter = '\Zenya\Api\Response' . '\\' . ucfirst($this->format);

		$this->setHeaders($formatter::$contentType);

		return $formatter::generate($this->toArray());
	}

    static public function throwExceptionIfUnsupportedFormat($format)
    {
        if (!in_array(strtolower($format), self::$formats)) {
			throw new Exception("Format ({$format}) not supported.", 404); // TODO: maybe 406?
        }
	}

    /**
     * Get all the response formats available.
     *
     * @return array
     */
    static public function getFormats()
    {
        return self::$formats;
    }

    public function setHeaders($contentType)
    {
		header('X-Powered-By: ' . $this->server->version, true, $this->server->httpCode);

		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header('Vary: Accept');

		if(!$this->server->debug) {
			header('Content-type: ' . $contentType);
		}
		// It will be called downloaded.pdf
		#header('Content-Disposition: attachment; filename="downloaded.pdf"');
		// The PDF source is in original.pdf
		#readfile('original.pdf');
    }

    public function _addHeaderFromException()
    {
        $r = $this->getResponse();
        if ($r->isException()) {
            $stack = $r->getException();
            $e = is_array($stack) ? $stack[0] : $stack;
            if (is_a($e, 'Zenya_Api_Exception')) {
                //Zend_debug::dump($e);
                $r->setHttpResponseCode($e->getCode());
                #$resp->setHeader('X-Error', $e->getCode());
            }
        }
    }
	
	
		/*
	 * Hold alist of Http status used by Zenya_Api
	 * as per http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 *
	 * k=Code, Desc, Msg
	 */
	protected $_httpStatuses = array(
		200 => array('OK', 'The request has succeeded.'),
		// POST
		'201' => array('Created', 'The request has been fulfilled and resulted in a new resource being created.'),
		// Note: require to use ->setHeader("Location", "http://url/action/id")

		'202' => array('Accepted', 'The request has been accepted for processing, but the processing has not been completed.'),
		// DELETE
		'204' => array('No Content', 'Request fulfilled successfully.'),
		// Error
		'400' => array('Bad Request', 'Request is malformed.'),
		'401' => array('Unauthorized', 'Not Authenticated.'),
		'403' => array('Forbidden', 'Access to this ressource has been denied.'),
		'404' => array('Not Found', 'No ressource found at the Request-URI.'),
		'503' => array('Service Unavailable', 'The service is currently unable to handle the request due to a temporary overloading or maintenance of the server. Try again later.'),
	);

	/* depreciated */
	public function getHttp()
	{
		$status = $this->httpCode . ' ' . Zend_Http_Response::responseCodeAsText($this->httpCode);
		$description = isset($this->_httpStatuses[$this->httpCode][1]) ? $this->_httpStatuses[$this->httpCode][1] : $status . ' (not implemented)';

		// Zend_Http_Response::fromString
		return array(
			'status' => $status,
			'description' => $description,
		);
	}
	
}