<?php

/** @see Zendya\Api\Response */
namespace Zenya\Api\Output;

use Zenya\Api\Output\Adapter;

class Php extends Adapter
{

    /**
     * Holds the media type for the output.
     * @var string
     * @see http://www.ietf.org/rfc/rfc2046.txt
     * @see http://www.ietf.org/rfc/rfc3676.txt
      */
    public $contentType = 'text/plain';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        return $this->dump(array($rootNode=>$data), true);
    }

    public function dump($data)
    {
        // return print_r($data, true);
        return $this->htmldump($data);
    }

    public function htmldump($data, $height="9em")
    {
        # "<pre style=\"border: 1px solid #000; height: {$height}; overflow: auto; margin: 0.5em;\">";
        var_dump($data);
        # echo "</pre>\n";
    }

}
