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

namespace Zenya\Api\Output;

use Zenya\Api\Output\Adapter;

class Xml extends Adapter
{

    /**
     * Holds the media type for the output.
     * @var string
     * @see http://www.ietf.org/rfc/rfc3023.txt
     */
    public $contentType = 'text/xml';

    /**
     * @var	string
     */
    protected $encoding = 'utf-8'; #'iso-8859-1';

    /**
     * @var	\SimpleXMLElement
     */
    protected $xml;

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode="root")
    {
        $str = sprintf('<?xml version="1.0" encoding="%s"?><%s />',
                    $this->encoding,
                    $rootNode
                );

        $Xml = new \SimpleXMLElement($str);
        $this->arrayToXml($Xml, $data);

        return $this->validate(
            $Xml->asXML()
        );
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
            // replace numeric index key to 'item' e.g. <results<item>...</item></results>
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
                $xml->addChild(
                    $k,
                    htmlentities($v, ENT_NOQUOTES, $this->encoding)
                );
            }
        }
    }

    /**
     * Sanitize
     *
     * @param  string $str
     * @return string
     */
    protected function validate($xml)
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

/*
if ( !function_exists( 'xmlentities' ) ) {

    public function xmlentities( $string )
    {
        $not_in_list = "A-Z0-9a-z\s_-";

        return preg_replace_callback( "/[^{$not_in_list}]/" , function($CHAR) {
        if ( !is_string( $CHAR[0] ) || ( strlen( $CHAR[0] ) > 1 ) ) {
            die( "function: 'get_xml_entity_at_index_zero' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type." );
        }
        switch ($CHAR[0]) {
            case "'":    case '"':    case '&':    case '<':    case '>': case ':': case '/':
                return htmlspecialchars( $CHAR[0], ENT_QUOTES );    break;
            default:
                return numeric_entity_4_char($CHAR[0]);                break;
        }
            }, $string );
    }

    public function get_xml_entity_at_index_zero( $CHAR )
    {
        if ( !is_string( $CHAR[0] ) || ( strlen( $CHAR[0] ) > 1 ) ) {
            die( "function: 'get_xml_entity_at_index_zero' requires data type: 'char' (single character). '{$CHAR[0]}' does not match this type." );
        }
        switch ($CHAR[0]) {
            case "'":    case '"':    case '&':    case '<':    case '>':
                return htmlspecialchars( $CHAR[0], ENT_QUOTES );    break;
            default:
                return numeric_entity_4_char($CHAR[0]); break;
        }
    }

    public function numeric_entity_4_char( $char )
    {
        return "&#".str_pad(ord($char), 3, '0', STR_PAD_LEFT).";";
    }
}
*/
