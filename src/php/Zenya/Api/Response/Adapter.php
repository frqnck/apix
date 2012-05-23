<?php
namespace Zenya\Api\Response;

interface Adapter {

    #static $contentType;

    /**
     * Data encoder.
     *
     * @param  array  $data     Response data to encode
     * @param  string $rootNode The rootNode element.
     * @return string
     */
    public function encode(array $data, $rootNode);

}
