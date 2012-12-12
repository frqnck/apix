<?php
namespace Apix\Output;

class Php extends AbstractOutput
{
    public $debug = false;

    /**
     * {@inheritdoc}
     * @see http://www.ietf.org/rfc/rfc2046.txt
     * @see http://www.ietf.org/rfc/rfc3676.txt
      */
    protected $content_type = 'text/plain';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode=null)
    {
        if (null !== $rootNode) {
            $data = array($rootNode => $data);
        }

        return false === $this->debug
                ? $this->serialize($data)
                : $this->dump($data);
    }

    public function serialize($data)
    {
        return serialize($data);
    }

    public function dump($data)
    {
        return print_r($data, true);
    }

}
