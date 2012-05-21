<?php
/**
 * Zenya API XML Response
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

class Xml implements Adapter
{
	static $contentType = 'application/xml';	

	protected $_xml = null;
	
	public static function generate(array $data)
	{
		$r = new self($data);
		if(extension_loaded('tidy')) {
			return $r->validate($r->_xml->asXML());
		}
		return $r->_xml->asXML();
	}

	public function __construct(array $data)
	{
		$str = '<?xml version="1.0" encoding="utf-8"?>';
		$str .= '<zenya></zenya>'; // TODO: add attrib version maybe!
		$this->_xml = simplexml_load_string($str);
		#array_walk_recursive($data, array($this->_xml, 'addChild'));

		#$this->_xml .= '<item>' . self::_xml($data['zenya']) . '</item>';
		$this->_recursivelyAppend($data['zenya'], $this->_xml);

		#Zend_debug::dump( $this->_xml[0] );
	}

	protected function _recursivelyAppend(array $results, $xml)
	{
		foreach ($results as $k => $v) {
			if (is_int($k))
				$k = 'item';
			if (is_array($v)) {
				if($k == '@attributes') {
					foreach($v as $k => $v) {
						$xml->addAttribute($k, $v);
					}
				} else {
					$child = $xml->addChild($k);
					$this->_recursivelyAppend($v, $child);
				}
			} else {
				$xml->addChild($k, htmlentities($v, ENT_NOQUOTES, 'UTF-8'));
			}
		}
	}

	public function validate($xml)
	{
			$tidy = new \tidy();
			$conf = array(
				'clean' => true,
				'input-xml' => true,
				'output-xml' => true,
				'indent' => true, // BUG!!!
				'wrap' => 80,
			);
			$tidy->parseString($xml, $conf, 'utf8');
			$tidy->cleanRepair();
			#return $tidy->html()->value;
			return $tidy->value; // with DOCTYPE
			#return tidy_get_output($tidy);
	}

}