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
    public function encode(array $data, $rootNode=null)
    {
        // Encode <, >, ', &, and " for RFC4627-compliant JSON.
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

        // @codeCoverageIgnoreStart
        // if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
        //     $options = $options | JSON_PRETTY_PRINT;
        // }
        // @codeCoverageIgnoreEnd

        if (null !== $rootNode) {
            $data = array($rootNode => $data);
        }

        return json_encode($data, $options);
    }

}
