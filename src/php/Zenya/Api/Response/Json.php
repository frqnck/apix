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

	public static function generate(array $data)
	{
		#$autoloader = \Zend_Loader_Autoloader::getInstance();

		require_once '/www/php_libs/Zend/latest/library/Zend/Json.php';
		require_once '/www/php_libs/Zend/latest/library/Zend/Json/Expr.php';

		
		$json = Zend_Json::encode($data);
		if (isset($_REQUEST['indent']) && $_REQUEST['indent'] == '1') {
			return \Zend_Json::prettyPrint($json, array('indent' => "  "));
		}
		return $json;
	}

}