<?php
namespace Zenya\Api\Output;

/**
 * Interface for response output.
 *
 * @author Franck Cassedanne <fcassedanne@info.com>
 */
abstract class Adapter
{

    /**
     * Holds the media type for the output.
     * @var string
     */
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
     * Returns the current mime/media/content type.
     *
     * @return string
     */
    public function getContentType()
    {
        if (null === $this->contentType) {
            throw new \RuntimeException('Content-Type missing from this implementation.');
        }

        return $this->contentType;
    }

}
