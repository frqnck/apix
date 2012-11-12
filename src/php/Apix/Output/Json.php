<?php
namespace Apix\Output;

class Json extends AbstractOutput
{

    /**
     * {@inheritdoc}
     * @see http://www.ietf.org/rfc/rfc4627.txt
     */
    protected $content_type = 'application/json';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        if (isset($_REQUEST['indent']) && $_REQUEST['indent'] == '1') {

            // @codeCoverageIgnoreStart
            if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
                return json_encode(array($rootNode=>$data), JSON_PRETTY_PRINT);
            }
        }
        // @codeCoverageIgnoreEnd

        return json_encode(array($rootNode=>$data));
    }

}
