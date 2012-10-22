<?php
namespace Apix\Output;

use Apix\Output\Adapter;

class Json extends Adapter
{

    /**
     * Holds the media type for the output.
     * @var string
     * @see http://www.ietf.org/rfc/rfc4627.txt
     */
    public $contentType = 'application/json';

    /**
     * {@inheritdoc}
     */
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
