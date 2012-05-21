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


class Server extends Listener
{
	public $debug = true;
	public $version = 'Zenya/0.2.1';

	public $httpCode = 200;
	public $resourceName;

	public function __construct()
	{
	}

	public function run()
	{
		$config = array(
			'route_prefix' => '@^(/index.php)?/api/v(\d*)@i', // regex
			'routes' => array(
				#'/:controller/paramName/:paramName/:id' => array(),
				#'/:controller/test' => array('class'=>'test'),
				'/:controller/:param1/:param2' => array(
					#'controller' => 'BlankResource',
					'className' => 'Zenya\Api\Resource\BlankResource',
					'classArgs' => array('arg1'=>'test', 'arg2'=>'test2'))
			),
			// need DIC here!!
			'listeners' => array(
				// pre-processing stage
				'early' => array(
					'new Listener\Auth',
					'new Listener\Acl',
					'new Listener\Log',
					'new Listener\Mock'
				),

				// post-processing stage
				'late'=>array(
					'new Listener\Log',
				),
			)
		);
		
		try {
			// Request
			$this->request = new Request;

			// get path without the route prefix
			$path = preg_replace($config['route_prefix'], '', $this->request->getUri());

			// Routing
			$this->route = new Router($config['routes'], array(
				'method' => $this->request->getMethod(),
				'path'	 => $path,
				'className'=>null,
				'classArgs'=>null
			));
			$this->route->map($path);

			$name = explode('.', $this->route->controller);
			$this->route->name = $name[0];
			$this->route->format = count($name)>1 ? end($name) : null;
			$this->route->params = $this->route->params+$this->request->getParams();

			// set format from extension then from html head
			if(isset($this->route->format)) {
				$format = $this->route->format;
			} elseif(isset($_GET['format'])) {
				$format = $_GET['format'];
			} else {
				$accept = $this->request->getHeader('Accept');
				switch (true) {
					
					// 'application/json'
					case (strstr($accept, '/json')):
						$format = 'json';
					break;

					// 'text/xml', 'application/xml'
					case (strstr($accept, '/xml')
						&& (!strstr($accept, 'html'))):
						$format = 'xml';
					break;
				
					default:
						$format = Response::DEFAULT_FORMAT;
				}
			}
			$this->route->format = $format;
			Response::throwExceptionIfUnsupportedFormat($format);
			
			// attach early listeners @ pre-processing
			$this->stage = 'early';
			$this->addAllListeners('server', 'early');
			$this->stage = 'late';

			// Process with the requested resource
			$resources = new Resource(array('BlankResource' => array('class'=>'Zenya\Api\Resource\BlankResource', 'args'=>array('test'))));

			$this->results = $resources->callNew($this->route);

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

		} catch (Exception $e) {
			$this->results = array(
				'error' => $e->getMessage(),
			);
			$this->httpCode = $e->getCode() ? $e->getCode() : 500;

			// attach late listeners @ exceptions
			$this->addAllListeners('server', 'exception');
		}

		$response = new Response($this, $this->route->format);
		echo $response->send();

		// attach late listeners @ post-processing
		$this->addAllListeners('server', 'late');

		#print_r($this);
		exit;


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

}