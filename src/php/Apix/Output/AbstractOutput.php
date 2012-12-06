<?php
namespace Apix\Output;

abstract class AbstractOutput
{

    /**
     * Holds the media-type .
     * @var string
     */
    protected $content_type = null;

    /**
     * Encode an array of data.
     *
     * @param  array       $data     Response data to encode
     * @param  string|null $rootNode The rootNode element.
     * @return string
     */
    abstract public function encode(array $data, $rootNode=null);

    /**
     * Returns the content-type.
     *
     * @return string
     * @throws \RuntimeException If self::contentType === null
     */
    public function getContentType()
    {
        if (null === $this->content_type) {
            throw new \RuntimeException('Missing a content-type.');
        }

        return $this->content_type;
    }

}
