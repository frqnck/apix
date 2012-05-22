<?php
/**
 * Zenya API JSON Response
 *
 * e.g.
 * <Zenya_Model_Dummy generator="zend" version="1.0">
 * <foo>
 * <Zend_Version>1.10.8</Zend_Version>
 * <method><key_0>Zenya_Model_Dummy::foo</key_0></method>
 * <status>success</status>
 * </foo>
 * </Zenya_Model_Dummy>
 *
 * @category	Zenya
 * @package		Zenya_Api
 * @subpackage	Response
 * @copyright	Copyright (c) 2010 Info.com Ltd. (http://www.info.com)
 * @version		$Id$
 */

/** @see Zendya\Api\Response */
namespace Zenya\Api\Response;

/** @see Zendya_Api_Response */
#require_once '/www/php_libs/Zend/latest/library/Zend/Loader.php';
#require_once '/www/php_libs/Zend/latest/library/Zend/Loader/Autoloader.php';

class Json implements Adapter
{
	static $contentType = 'application/json';

	public function encode(array $data, $rootNode)
	{
		if (isset($_REQUEST['indent']) && $_REQUEST['indent'] == '1') {
			if(version_compare(PHP_VERSION, '5.4.0') >= 0) {
				return json_encode(array($rootNode=>$data), JSON_PRETTY_PRINT);
			}
		}

		return json_encode(array($rootNode=>$data));
	}

	public function decode($jsonStr, $assoc=true)
	{
		return json_decode($jsonStr, $assoc);
	}

}