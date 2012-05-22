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

class Resource extends Listener
{

	/**
	 * Stores the resources'objects.
	 *
	 * @var	array
	 */
	protected $_resources = array();

    /**
	 * Import given objects
     *
     * @param	array	$resources
     */
	public function __construct(array $resources)
	{
        $this->_resources = $resources;
	}
	
	/**
	 * Check and return a sanitized/clean resource name (short and public)
	 *
	 * @params	string	$name	A resource name to check
	 * @return	string
	 * @throws	Zenya\Api\Exception	If it doesn't not exist.
	 */
	public function getPublicAppelation($name)
	{
		$name = ucfirst($name);
		if (array_key_exists($name, $this->_resources)) {
			return $name;
		}
		
		throw new Exception("Invalid resource's name specified ({$name})", 404);
	}
	
	/**
	 * Return the class name for a resource (long and private)
	 *
	 * @params	string	$name
	 * @return	string
	 * @throws	Zenya\Api\Exception
 	 * @see		Zenya\Api\Resource::getPublicAppelation
	 */
	public function getInternalAppelation($name)
	{
		$short = $this->getPublicAppelation($name);
		return $this->_resources[$short];
	}
	
	/**
	 * Get the full resources array
	 *
	 * @return	array	array of resources.
	 */
	public function getResources()
	{
		return $this->_resources;
	}

	/**
	 * Act as a data mapper, check required params, etc...
	 *
	 * @return void
	 * @throws Zenya_Api_Exception
	 */
	private function checkRequirments($method, array $params)
	{
		foreach ($this->_requirements as $k => $v) {
			if (in_array($method, $v)) {
				if (true === is_int($k))
					continue;
				if (!array_key_exists($k, $params) || empty($params[$k])) {
					throw new Exception("Required {$method} parameter \"{$k}\" missing in action.", 400);
				}
			}
		}
	}

	/**
	 * Call a resource
	 *
	 * @params	string	$name	Name of the resource
	 * @return	array
 	 * @throws	Zenya\Api\Exception
	 * @see		Zenya\Api\Resource::getPublicAppelation
	 */	
	public function call(\Zenya\Api\Server $server)
	{
		$this->server = $server;
		
		// attach late listeners @ post-processing
		$this->addAllListeners('resource', 'early');

		$route = $this->server->route;

		$class = self::getInternalAppelation($route->name);

		/* --- Relection --- */
		$refClass = new \ReflectionClass($class['class']);

		// Array of HTTP Methods to CRUD verbs.
		$_crud = array(
			'POST'		=> 'create',
			'GET'		=> 'read',
			'PUT'		=> 'update',
			'DELETE'	=> 'delete',
			'HEAD'		=> 'help',
			'OPTIONS'	=> 'test'
		);

		$route->method = 'HEAD';

		$route->action = $_crud[$route->method] . 'ApiResource';
/*
		switch() {
			case 'HEAD':
			case 'OPTIONS':
				
		}
*/
 		if($route->method == 'HEAD' || $route->method == 'OPTIONS'
			&& !$refClass->hasMethod($route->action)
		) {
			echo $route->action;
			return $this->helpAction();
		}

		$refMethod = $refClass->getMethod($route->action);
		if ( !in_array($refMethod, $refClass->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC))
			&& !$refMethod->isConstructor()
			&& !$refMethod->isAbstract()
		) {
			# TODO: move this from here...
			@header('Allow: ' . implode(', ', $refClass->getMethods()), true);

			throw new Exception("Invalid resource's method ({$route->method}) specified.", 405);
		}
		
		$params = array();
		foreach($refMethod->getParameters() as $k => $param) {
			if (!$param->isOptional()
				&& !array_key_exists($param->name, $route->params) 
				&& empty($route->params[$param->name])
			) {
				throw new Exception("Required {$route->method} parameter \"{$param->name}\" missing in action.", 400);
			} else if(isset($route->params[$param->name])) {
				$params[$param->name] = $route->params[$param->name];
			}
		}

		return call_user_func_array(array(new $route->className($route->classArgs), $route->action), $params);
	}

	/**
	 * HTTP OPTIONS: Help action handler
	 * 
	 * The OPTIONS method represents a request for information about the
	 * communication options available on the request/response chain
	 * identified by the Request-URI. This method allows the client to determine
	 * the options and/or requirements associated with a resource, 
	 * or the capabilities of a server, without implying a resource action or 
	 * initiating a resource retrieval.
	 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
	 *
	 * @todo	TODO
	 * 
	 * @expect	client request  has an entity-body (indicated by Content-Length or Transfer-Encoding) then client's Content-Type must be set.
	 * 
 	 * @cacheable false
	 */
	final public function helpAction()
	{
		$request = $this->server->request;

		echo $_SERVER['APPLICATION_ENV'];
		
		
		
		Server::d($request);

		if($this->server->route->path == '*') {
			// apply to the whole server

			if( $request->hasHeader('Content-Length')
				|| $request->hasHeader('Transfer-Encoding')
			) {

				// TODO: process the $this->server->body!

			} else {
				// resource specific
				// A server that does not support such an extension MAY discard the request body.
				@header('Allow: ' . implode(', ', $refClass->getMethods()), true);
				if( null === $request->getRawBody()) {
					header('Content-Length: 0');
				}
					
				/*		
				$man = $this->getParam('resource');
				$resource = Zenya_Api_Resource::getInternalAppelation($man);
				$help = new Zenya_Api_ManualParser($resource, $man, 'api_');
				$this->_output = $help->toArray();
				*/		
			}

		}

		return array('Test Handler, handles HTTP OPTIONS method');
	}
	
	/**
	 * HTTP HEAD: test action handler 
	 *
	 * The HEAD method is identical to GET except that the server MUST NOT return
	 * a message-body in the response. The metainformation contained in the HTTP
	 * headers in response to a HEAD request SHOULD be identical to the information
	 * sent in response to a GET request. This method can be used for obtaining 
	 * metainformation about the entity implied by the request without transferring 
	 * the entity-body itself. This method is often used for testing hypertext links 
	 * for validity, accessibility, and recent modification.
	 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
	 * 
	 * @return	null
  	 * @cacheable true
	 */
	final public function testAction()
	{
		
		// identical to get without the body output.
		// shodul proxy to the get method!? 
			
		return null; // MUST NOT return a message-body in the response
	}

}