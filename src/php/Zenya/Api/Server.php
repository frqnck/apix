<?php
/**
 * Copyright (c) 2011 Zenya.com
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Zenya nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package     Zenya
 * @subpackage  ApiServer
 * @author      Franck Cassedanne <fcassedanne@zenya.com>
 * @copyright   2011 zenya.com
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://zenya.github.com
 * @version     @@PACKAGE_VERSION@@
 */

namespace Zenya\Api;

class Container
{

	protected $params = array();

	function __set($key, $value)
	{
		$this->params[$key] = $value;
	}

	function __get($key)
	{
		if (!isset($this->params[$key])) {
			throw new InvalidArgumentException(sprintf('Value "%s" is not defined.', $key));
		} if (is_callable($this->params[$key])) {
			return $this->params[$key]($this);
		} else {
			return $this->params[$key];
		}
	}

	function asShared($callable)
	{
		return function ($c) use ($callable) {
					static $object;
					if (is_null($object)) {
						$object = $callable($c);
					}
					return $object;
				};
	}

}

class Server
{

	public $resourceName;
	public $debug = false;

	public function __construct()
	{
		
	}

	public function run()
	{

		try {

			$this->request = new Request;

			// get path without the api prefix
			$api_prefix = "@^/rest/v(\d*)@i";
			$path = preg_replace($api_prefix, '', $this->request->getUri());
			$this->resourcePath = $path;
			
			// get teh path elements
			#$paths = explode('/', $path);
			#preg_sp('@@', $path, $paths);
			$paths = preg_split('/\//', $path, null, PREG_SPLIT_NO_EMPTY);

			// remove the script name 
			#$scriptName = explode('/', $_SERVER['SCRIPT_NAME']);
			$scriptNames = preg_split('/\//', $_SERVER['SCRIPT_NAME'], null, PREG_SPLIT_NO_EMPTY);
			foreach($scriptNames as $k => $v) {
				if ($v == $paths[$k]) {
					unset($paths[$k]);
				}
			}
			$paths = array_values($paths);

			// get resource name & extension
			$name = explode('.', $paths[0]);
			$this->resourceName = $name[0];
			$this->resourceExtension = count($name)>1 ? end($name) : null;

			// set format from extension then from html head
			if(!is_null($this->resourceExtension)) {
				$format = $this->resourceExtension;
			} else {
				$accept = $this->request->getHeader('Accept');
				switch (true) {
					case (strstr($accept, 'application/json')):
						$format = 'json';
					break;
				
					case (strstr($accept, 'application/xml')
						&& (!strstr($accept, 'html'))):
						$format = 'xml';
					break;
				
					default:
						$format = Response::DEFAULT_FORMAT;
				}
			}
			
			Response::throwExceptionIfUnsupportedFormat($format);

			$this->format = $format;
			
			// attach listeners
			#$this->request->attach(new Listener\Log);
			#$this->request->attach( new Listener\Mock() );
			#$this->request->attach(new Listener\Format);

			// AUTH
			// ACL
			// Log (request part, timing)
			$this->request->notify();



			// TODO getPathInfo!
			//obsever for the following: 
			// ContextSwitcher
			// Process with the requested resource
			$resource = new Resource(array('BlankResource' => 'Zenya\Api\Resource\BlankResource'));

			$this->results = $resource->call($this->resourceName, $this->request);

			$this->httpCode = 200;
			/*
			  if ($r->isException()) {
			  $ex = $r->getException();
			  $e = is_array($ex) ? $ex[0] : $ex;
			  if (is_a($e, 'Exception')) { // Zenya_Api_Exception
			  $this->httpCode = $e->getCode() ? $e->getCode() : 500;
			  $body = array('error' => $e->getMessage());
			  $r->setHttpResponseCode($this->httpCode);
			  }
			  } else {
			  $body = $Resource->getResource($params['action'], $req);
			  }
			 */


			// Post processing
			// ContextSwitcher
			// AUTH
			// ACL
			// Log (processing result)
		} catch (Exception $e) {
			$this->results = array(
				'error' => $e->getMessage(),
			);

			$this->setHttpCode($e->getCode() ? $e->getCode() : 500);
		}

		$response = new Response($this);
		echo $response->send();
		exit;



		// output the response
		header('content-type: text/javascript');
		echo json_encode($response);






		try {
			$Response = new Zenya_Api_Response($data);
			// send to stdout
			$out = $Response->output($data->format);
		} catch (Exception $e) {
			$out = 'ERROR: Exception at ' . __METHOD__ . ': ' . PHP_EOL
					. $e->getMessage() . PHP_EOL . $e->getTraceAsString();
		}

		$controller = Zend_Controller_Front::getInstance();
		$response = $controller->getResponse()->setBody($out);
		$response->renderExceptions(false);
		//$response->headersSentThrowsException = false;
		reg('logger')->info('Informational message');

		// send to stdout
		echo $out;
	}

	/**
	 * Set parameters
	 *
	 * @return void
	 */
	public function setParams(array $params)
	{
		$this->_rawParams = $params;
		$this->format = Response::outputFormat($params['format']);
		$this->action = strtolower($params['action']);
		$this->body = self::getResponseBody();

		// obsolete?
		//$this->setHttpCodeFromException();
	}

	/**
	 * Hold the output format (also used to set the default value).
	 * @var	integer	$int
	 */
	public function setHttpCode($int)
	{
		$this->httpCode = $int;
	}

}