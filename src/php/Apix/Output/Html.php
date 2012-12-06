<?php
namespace Apix\Output;

use Apix\View\Template,
    Apix\View\View,
    Apix\View\ViewModel,
    Apix\Model;

class Html extends AbstractOutput
{

    /**
     * {@inheritdoc}
     * @see http://www.ietf.org/rfc/rfc2854.txt
     */
    protected $content_type = 'text/html';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode=null)
    {
        if(null !== $rootNode) {
            $data = array($rootNode => $data);
        }

        return $this->recursivelyAppend($data);
    }

    /**
     * Append the data recursively...
     *
     * @param   array   $results
     * @return  string
     */
    protected function recursivelyAppend(array $results)
    {
        $out = '<ul>';
        foreach ($results as $k => $v) {
            $out .= "<li>$k: ";
            $out .= is_array($v) ? $this->recursivelyAppend($v, $k) : $v;
            $out .= '</li>';
        }
        $out .= '</ul>';

        return $out;
    }

}