<?php
namespace Apix\Output;

use Apix\Output\Adapter;

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
        return $this->dump(array($rootNode=>$data));
    }

    public function dump($data)
    {
        return print_r($data, true);
    }

    // public function htmldump($data, $height="9em")
    // {
    //     echo "<pre style=\"border: 1px solid #000; height: {$height}; overflow: auto; margin: 0.5em;\">";
    //     var_export($data);
    //     echo "</pre>\n";
    // }

}
