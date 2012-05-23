<?php

/** @see Zendya\Api\Response */
namespace Zenya\Api\Response;

class Php implements Adapter
{
    public static $contentType = 'text/plain';

    public function encode(array $data, $rootNode='root')
    {
        return print_r(array($rootNode=>$data), true);
    }

}
