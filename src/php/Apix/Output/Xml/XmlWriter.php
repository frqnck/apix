<?php
namespace Apix\Output\Xml;

use Apix\Output\Xml;

class XmlWriter extends Xml
{

    /**
     * XMLWriter handler
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        $x = new \XmlWriter();
        $x->openMemory();
        $x->startDocument($this->version, $this->encoding);
            $x->startElement($rootNode);
                $this->arrayToXmlWriter($x, $data);
            $x->endElement();
        $x->endDocument();

        return $x->outputMemory(true);
    }

    /**
     * Array to XMLWriter
     *
     * @param \XmlWriter        $xml
     * @param array             $array
     */
    protected function arrayToXmlWriter(\XmlWriter $x, array $array)
    {
        foreach ($array as $k => $v) {
            // replace numeric index key to 'item' e.g. <results><item>...</item></results>
            if (is_array($v)) {
                if (is_int($k)) {
                    $k = $this->items_key;
                }
                $x->startElement($k);
                $this->arrayToXmlWriter($x, $v);
                $x->endElement();
            } else {
                if (is_int($k)) {
                    $k = $this->item_key;
                }
                $x->writeElement($k, $v);
            }
        }
    }

}