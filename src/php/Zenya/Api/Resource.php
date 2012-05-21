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
	 * Stores all the resources available.
	 *
	 * @var	array
	 */
	protected $_resources = array();

    /**
     * Zenya\Api\Resource Constructor.
	 * 
	 * Import given object methods
     *
     * @param	array	$resources	Array of resources
     */

	public function __construct(array $resources)
	{
        $this->_resources = $resources;
	}
	
	/**
	 * Check and return a sanitized/clean resource name (short and public usage)
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
	 * Get Resource
	 *
	 * @params	string	$name	Name of the resource
	 * @return	array
 	 * @throws	Zenya\Api\Exception
	 * @see		Zenya\Api\Resource::getPublicAppelation
	 */
	public function call(\Zenya\Api\Router $route)
	{
		// attach late listeners @ post-processing
		$this->addAllListeners('resource', 'early');

		$class = self::getInternalAppelation($route->name);

		#$method = $request->method;
		#$params = $request->params;
		#$service = call_user_func_array(array($class, $local_method), $params);

		return new $class($route);
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

	
	public function callNew(\Zenya\Api\Router $route)
	{
		// attach late listeners @ post-processing
		$this->addAllListeners('resource', 'early');

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
		$route->action = $_crud[$route->method] . 'ApiResource';

		$refMethod = $refClass->getMethod($route->action);

		if ( !in_array($refMethod, $refClass->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC))
			&& !$refMethod->isConstructor()
			&& !$refMethod->isAbstract()
		) {
			# TODO: move this out of here...
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
	
	function replaceAndClean(/*query [, $arg1...$argN]*/){
   $args = func_get_args();
   if(count($args) == 1){
       return $args[0];
   }
   $query = array_shift($args);
   return vsprintf($query, $args);
}

}