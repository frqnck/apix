<?php

/** @see Zendya\Api\Response */
namespace Zenya\Api\Response;

class Php implements Adapter
{
	static $contentType = 'application/php';	

	public static function generate(array $data)
	{
		return print_r($data, true);
	}

}