<?php
namespace Apix\Output\Xml;

use Apix\Output\Xml;

class SimpleXml extends Xml
{

    /**
     * SimpleXML handler
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        $str = sprintf('<?xml version="%s" encoding="%s"?><%s />',
            $this->version,
            $this->encoding,
            $rootNode
        );

        $x = new \SimpleXMLElement($str);
        $this->arrayToSimpleXml($x, $data);

        return $x->asXML();
    }

    /**
     * Array to Simple XML
     *
     * @param \SimpleXMLElement $xml
     * @param array             $array
     */
    protected function arrayToSimpleXml(\SimpleXMLElement $x, array $array)
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                if (is_int($k)) {
                    $k = $this->items_key;
                }

                // @codeCoverageIgnoreStart
                // Attributes needs to be reviewd!!!
                #if ($k == '@attributes') {
                #    foreach ($v as $k => $v) {
                #        $x->addAttribute($k, $v);
                #    }
                // @codeCoverageIgnoreEnd
                #} else {
                    $child = $x->addChild($k);
                    $this->arrayToSimpleXml($child, $v);
                #}
            } else {
                if (is_int($k)) {
                    $k = $this->item_key;
                }

                // $xml->addAttribute(
                //     $k,
                //     htmlspecialchars($v, ENT_QUOTES, $this->encoding)
                // )
                $x->addChild($k, $this->specialChars($v));
            }
        }
    }

    /**
     * decode any special characters entities.
     * XmlWriter does this automatically.
     */
    public function specialChars($var)
    {
        return htmlspecialchars($var, ENT_COMPAT, $this->encoding);
    }

}