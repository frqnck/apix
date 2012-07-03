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
        return print_r(array($rootNode=>$data), true);
    }

}
