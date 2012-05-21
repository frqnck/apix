<?php
/**
 * @category	Zenya
 * @package	Zenya_Api
 * @subpackage	Response
 * @copyright	Copyright (c) 2010 Info.com Limited. (http://www.info.com)
 * @version	$Id$
 */
namespace Zenya\Api\Response;

interface Adapter {

	#static $contentType;	

	/**
     * Generate fromatted response data.
     *
     * @param array	$data response data unformated
     * @return string formatted output of the response
     */
    public static function generate(array $data);
	
}