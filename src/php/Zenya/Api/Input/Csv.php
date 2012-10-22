<?php

/** @see Zendya\Api\Response */
namespace Zenya\Api\Output;

use Zenya\Api\Output\Adapter;

class Csv extends Adapter
{

    /**
     * Holds the media type for the output.
     * @var string
     * @see http://www.ietf.org/rfc/rfc4180.txt
     */
    public $contentType = 'text/cvs';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
       return $this->toCSV($data);
    }

    protected function toCSV(array $data, array $colHeaders = array(), $asString = false)
    {
        $stream = ($asString)
            ? fopen("php://temp/maxmemory", "w+")
            : fopen("php://output", "w");

        if (!empty($colHeaders)) {
            fputcsv($stream, $colHeaders);
        }

        foreach ($data as $record) {
            if (!is_array($record)) {
                fputcsv($stream, $record);
            }
        }

        if ($asString) {
            rewind($stream);
            $returnVal = stream_get_contents($stream);
            fclose($stream);

            return $returnVal;
        } else {
            fclose($stream);
        }
    }

}
