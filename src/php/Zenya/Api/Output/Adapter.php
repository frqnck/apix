<?php
namespace Zenya\Api\Output;

abstract class Adapter
{
    protected $contentType = null;

    /**
     * Encode an array of data.
     *
     * @param  array  $data     Response data to encode
     * @param  string $rootNode The rootNode element.
     * @return string
     */
    abstract public function encode(array $data, $rootNode='root');

    /**
     * Get the ContentType
     *
     * @return string
     */
    public function getContentType()
    {
        if (is_null($this->contentType)) {
            throw new \Exception('Content-Type is missing from this implementation.');
        }

        return $this->contentType;
    }

}
