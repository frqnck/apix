<?php

namespace Zenya\Api\Output;

class Json extends Adapter
{
    public $contentType = 'application/json';

    public function encode(array $data, $rootNode='root')
    {
        if (isset($_REQUEST['indent']) && $_REQUEST['indent'] == '1') {
            if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
                return json_encode(array($rootNode=>$data), JSON_PRETTY_PRINT);
            }
        }

        return json_encode(array($rootNode=>$data));
    }

}