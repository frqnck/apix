<?php

namespace Zenya\Api\Resource;

class BlankResource #extends ResourceAbstract
{
	/*
	 * A public var.
	 */
	public $hello = 'World!!!';

	/*
	 * Another public var.
	 */
	public $results = array();

	/*
	 * A protected var
	 */
	protected $_protected = 'protected';

	/*
	 * A private var
	 */
	protected $_private = 'private';

	public function __construct($params)
	{
		$this->constructorParams = $params;
	}

	public function readApiResource($param1, $param2, $optional=null)
	{
		return array(
			'class' => __CLASS__,
			'constructorParams'=>$this->constructorParams,
			'method'=>__METHOD__,
			'methodParams'=> get_defined_vars()
		);
	}
	
	public function updateApiResource(array $params)
	{
		return array('method'=>__METHOD__, 'params'=>$params);
	}
	
}