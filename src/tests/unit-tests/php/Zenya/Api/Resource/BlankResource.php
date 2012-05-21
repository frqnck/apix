<?php

namespace Zenya\Api\Resource;

class BlankResource #extends ResourceAbstract
{
	/*
	 * Another public var.
	 */
	public $hello = 'World!!!';

	/*
	 * A public var.
	 */
	public $results = array();

	/*
	 * A private var
	 */
	protected $_protected = 'Checking protected var.';

	/*
	 * A private var
	 */
	protected $_private = 'Checking private var.';
	
	
	/**
	 * Stores the names and methods requirements.
	 *
	 * @var array
	 */
	protected $_requirements = array(
		'paramName' => array('GET'),
		array('PUT')
	);

	public function __construct($params)
	{
		$this->constructParams = $params;
	}

	public function readApiResource($params, $another, $optional=null)
	{
		return array('method'=>__METHOD__, 'params'=>$params, 'constructParams'=>$this->constructParams);
	}
	
	public function updateApiResource(array $params)
	{
		return array('method'=>__METHOD__, 'params'=>$params);
	}
	
}