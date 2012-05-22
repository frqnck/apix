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

namespace Zenya\Api\Resource;

Abstract class ResourceAbstract implements \IteratorAggregate
{
	
	/**
	 * Return the output array.
	 *
	 * @return	array
	 */
	#public function toArray()
	public function getIterator()
	{
		return new \ArrayIterator($this);
	}
	
	/**
	 * Constructor.
	 *
	 * $param	string	$method
 	 * $param	array	$params
	 * @return void
	 * @throws Zenya\Api\Resource\Exception
	 */
	 
	/*
	final public function __construct(\Zenya\Api\Router $route)
	{
		$method = $route->method;
		$params = $route->params;

		if (!in_array($method, $this->getMethods())) {
			# TODO: move this out of here...
			@header('Allow: ' . $this->_getStringOfMethods(), true);

			throw new Exception("Invalid resource's method ({$method}) specified.", 405);
		}

		$this->checkRequirments($method, $params);

		// Array of HTTP Methods to CRUD verbs.
		$_crud = array(
			'POST'		=> 'create',
			'GET'		=> 'read',
			'PUT'		=> 'update',
			'DELETE'	=> 'delete',
			'HEAD'		=> 'help',
			'OPTIONS'	=> 'test'
		);
		
		$local_method = $_crud[$method] . 'ApiResource';
		$this->$local_method($params);
	}
*/
	/**
	 * Help Handler, handles HTTP HEAD method
	 *
	 * @todo	TODO
	 * @return	array
	 */
	final public function helpApiResource()
	{
		@header('Allow: ' . $this->_getStringOfMethods(), true);

		/*		
		$man = $this->getParam('resource');
		$resource = Zenya_Api_Resource::getInternalAppelation($man);
		$help = new Zenya_Api_ManualParser($resource, $man, 'api_');
		$this->_output = $help->toArray();
		*/		
		// TODO: add OPTIONS handler (help) here.
		return array('Help Handler, handles HTTP HEAD method');
	}

	/**
	 * Test Handler, handles HTTP OPTIONS method
	 *
	 * @todo	TODO
	 * @return	array
	 */
	final public function testApiResource()
	{
		return array('Test Handler, handles HTTP OPTIONS method');
	}
	
	/**
	 * Get all the methods available, include the generic ones.
	 *
	 * @return array
	 */
	private function getMethods()
	{
		$methods = array();
		foreach ($this->_requirements as $k => $v) {
			$methods = array_merge($methods, $v);
		}
		$methods = array_unique($methods);
		return array_merge($methods, array('HEAD', 'OPTIONS'));
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
	 * Return string with all the available methods.
	 *__toString
	 * @return	string
	 */
	private function _getStringOfMethods()
	{
		return implode(', ', $this->getMethods());
	}
	
}