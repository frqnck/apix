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
     * @var string
     */
    protected $version = '1.0';

    /**
     * @var	string
     */
    protected $encoding = 'UTF-8';

    /**
     * @var string
     */
    protected $item_key = 'item';

    /**
     * @var string
     */
    protected $items_key = 'items';

    /**
     * Factory encoder.
     * @codeCoverageIgnore
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        if (extension_loaded('xmlwriter')) {
            $xml = new Xml\XmlWriter;
        } else {
            // SimpleXml is a default PHP extension
            $xml = new Xml\SimpleXml;
        }

        return $xml->encode($data, $rootNode);
    }

    /**
     * Converts a boolean value to its string representation.
     *
     * @param  mixed        $var
     * @return string|mixed String Either 'True', 'False' as string or the initial value as is.
     */
    public function booleanString($var)
    {
        return is_bool($var)
                ? ($var ? 'True' : 'False')
                : $var;
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
