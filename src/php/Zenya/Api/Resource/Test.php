<?php

namespace Zenya\Api\Resource;

class BlankResource extends ResourceAbstract
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

	public function readApiResource(array $params)
	{
		$this->results = array(__METHOD__);
	}
	
	public function updateApiResource(array $params)
	{
		$this->results = array('method'=>__METHOD__, 'params'=>$params);
	}
	
}