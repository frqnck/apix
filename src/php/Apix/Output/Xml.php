<?php
namespace Apix\Output;

class Xml extends AbstractOutput
{

    /**
     * {@inheritdoc}
     * @see http://www.ietf.org/rfc/rfc3023.txt
     */
    protected $content_type = 'text/xml';

    /**
     * @var	string
     */
    #protected $encoding = 'iso-8859-1';
    protected $encoding = 'utf-8';

    /**
     * @var	\SimpleXMLElement
     */
    private $xml = null;

    /**
     * @var string
     */
    private $item_key = 'item';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode="root")
    {
        $str = sprintf('<?xml version="1.0" encoding="%s"?><%s />',
            $this->encoding,
            $rootNode
        );

        $xml = new \SimpleXMLElement($str);
        $this->arrayToXml($xml, $data);

        return $xml->asXML();
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
            // replace numeric index key to 'item' e.g. <results><item>...</item></results>
            if (is_int($k)) {
                $k = $this->item_key;
            }

            if (is_array($v)) {

                // @codeCoverageIgnoreStart
                // Attributes needs to be reviewd!!!
                #if ($k == '@attributes') {
                #    foreach ($v as $k => $v) {
                #        $xml->addAttribute($k, $v);
                #    }
                // @codeCoverageIgnoreStart
                #} else {
                    $child = $xml->addChild($k);
                    $this->arrayToXml($child, $v);
                #}
            } else {
                // $xml->addAttribute(
                //     $k,
                //     htmlspecialchars($v, ENT_QUOTES, $this->encoding)
                // )
                $xml->addChild(
                    $k,
                    htmlspecialchars($v, ENT_QUOTES, $this->encoding)
                    #htmlentities($v, ENT_NOQUOTES, $this->encoding)
                );
            }
        }
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
