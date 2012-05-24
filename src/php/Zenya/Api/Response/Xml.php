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
    /**
     * @var string
     */
    public static $contentType = 'text/xml';

    /**
     * @var	string
     */
    public $encoding = 'utf-8'; #'iso-8859-1';

    /**
     * @var	\SimpleXMLElement
     */
    protected $xml;

    /**
     * Convert
     *
     * @param array  $data
     * @param string $rootNode The root node
      * @return	string
     */
    public function encode(array $data, $rootNode="root")
    {
        $str = sprintf('<?xml version="1.0" encoding="%s"?><%s />',
                    $this->encoding,
                    $rootNode
                );

        $Xml = new \SimpleXMLElement($str);
        $this->arrayToXml($Xml, $data);

        return $this->validate($Xml->asXML());
    }

    /**
     * Convert an XML string to its array representation
     *
     * @param string  $str   An XML string.
     * @param boolean $assoc Convert objects to array.
      * @return	array
     */
    public function decode($xmlStr, $assoc=true)
    {
        /*
            $array = json_decode(json_encode($xmlStr), true);

            foreach ( array_slice($array, 0) as $key => $value ) {
                if ( empty($value) ) $array[$key] = NULL;
                elseif ( is_array($value) ) $array[$key] = toArray($value);
            }

            return $array;
        */

        // only for UTF-8
        return json_decode(json_encode((array) simplexml_load_string($xmlStr)), $assoc);
    }

    /**
     * Array to XML conversion
     *
     * @param \SimpleXMLElement $xml
     * @param array             $array
     */
    protected function arrayToXml(\SimpleXMLElement $xml, array $array)
    {
        foreach ($array as $k => $v) {
            if (is_int($k)) {
                $k = 'item';
            }
            if (is_array($v)) {
                if ($k == '@attributes') {
                    foreach ($v as $k => $v) {
                        $xml->addAttribute($k, $v);
                    }
                } else {
                    $child = $xml->addChild($k);
                    $this->arrayToXml($child, $v);
                }
            } else {
                $xml->addChild($k, htmlentities($v, ENT_NOQUOTES, $this->encoding));
            }
        }
    }

    /**
     * Sanitize
     *
     * @param  string $str
     * @return string
     */
    public function validate($xml)
    {
        if (extension_loaded('tidy')) {
            $tidy = new \tidy();
            $conf = array(
                'clean'			=> true,
                'input-xml'		=> true,
                'output-xml'	=> true,
                'indent'		=> true, // TODO: check possible issue/bug here!
                'wrap'			=> 80,
            );
            $tidy->parseString($xml, $conf);
            $tidy->cleanRepair();

            $xml = $tidy->value; // output with a DOCTYPE
            #$xml = $tidy->html()->value;
            #$xml = tidy_get_output($tidy);
        }

        return $xml;
    }

}
