<?php
namespace Apix\Output;

/**
 * Interface for response output.
 *
 * @author Franck Cassedanne <fcassedanne@info.com>
 */
abstract class AbstractOutput
{

    /**
     * Holds the media type for the output.
     * @var string
     */
    protected $content_type = null;

    /**
     * Encode an array of data.
     *
     * @param  array  $data     Response data to encode
     * @param  string $rootNode The rootNode element.
     * @return string
     */
    abstract public function encode(array $data, $rootNode='root');

    /**
     * Returns the content-type.
     *
     * @return  string
     * @throws  \RuntimeException If [self::contentType === null]
     */
    public function getContentType()
    {
        if (null === $this->content_type) {
            throw new \RuntimeException('Content-Type missing from this implementation.');
        }

        return $this->content_type;
    }

}
