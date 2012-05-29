<?php

/** @see Zendya\Api\Response */
namespace Zenya\Api\Output;

class Php extends Adapter
{
    public $contentType = 'text/plain';

    public function encode(array $data, $rootNode='root')
    {
        return print_r(array($rootNode=>$data), true);
    }

}
