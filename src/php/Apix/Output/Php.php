<?php
namespace Apix\Output;

class Php extends AbstractOutput
{

    /**
     * {@inheritdoc}
     * @see http://www.ietf.org/rfc/rfc2046.txt
     * @see http://www.ietf.org/rfc/rfc3676.txt
      */
    protected $content_type = 'text/plain';

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
