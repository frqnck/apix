<?php
namespace Apix\Output;

class Lst extends AbstractOutput
{

    /**
     * {@inheritdoc}
     * @see http://www.ietf.org/rfc/rfc2854.txt
     */
    protected $content_type = 'text/html';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        return $this->_recursivelyAppend(
            array($rootNode => $data)
        );
    }

    protected function _recursivelyAppend(array $results)
    {
        $out = '<ul>';
        foreach ($results as $k => $v) {
            $out .= "<li>$k: ";
            $out .= is_array($v) ? $this->_recursivelyAppend($v, $k) : $v;
            $out .= '</li>';
        }
        $out .= '</ul>';

        return $out;
    }

}