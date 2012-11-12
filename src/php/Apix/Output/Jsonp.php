<?php
namespace Apix\Output;

use Apix\Output\Json;

class Jsonp extends AbstractOutput
{

    /**
     * {@inheritdoc}
     * @see http://www.rfc-editor.org/rfc/rfc4329.txt
     */
    protected $content_type = 'application/javascript';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        $cb = isset($_REQUEST['callback']) && !empty($_REQUEST['callback'])
                        ? $_REQUEST['callback']
                        : $rootNode;

        $json = new Json();
        $str = $json->encode($data, $rootNode);

        return "{$cb}({$str});";
    }

}
