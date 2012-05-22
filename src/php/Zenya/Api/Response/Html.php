<?php
/**
 * Zenya API HTML Response
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

class Html implements Adapter
{
	static $contentType = 'application/html';	

	protected $_html = null;

	/**
	 * Encode an array to HTML (list elements).
	 * 
	 * @param	array	$data
	 * @param	string	$rootNode	The root node
 	 * @return	string 
	 */
	public function encode(array $data, $rootNode='root')
	{
		$out = $this->_recursivelyAppend(array($rootNode=>$data));
		if(extension_loaded('tidy')) {
			return $this->validate($out);
		} else {
			return $out;
		}
	}
	
	protected function _recursivelyAppend(array $results)
	{
		$out = '<ul>';
		foreach ($results as $k => $v) {
			$out .= "<li>$k: ";
			$out .= is_array($v) ? $this->_recursivelyAppend($v, $k) : $v;
			$out .= '</li>';
		}
		$out .= '</ul>';
		return $out;
	}
	
	public function validate($html)
	{
		$tidy = new \tidy();
		$conf = array(
			// PHP Bug: commenting out 'indent' (with true or false)
			// for some weird reason does chnage the Transfer-Encoding!
			'indent'			=> true,
			'tidy-mark'			=> false,
			'clean'				=> true,
			'output-xhtml'		=> false,
			'show-body-only'	=> true,
		);
		$tidy->parseString($html, $conf, 'UTF8');
		$tidy->cleanRepair();
		
		return $tidy->value; // with DOCTYPE
		#return $tidy->html()->value;
		#return tidy_get_output($tidy);
	}

}