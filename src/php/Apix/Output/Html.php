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
        return $this->validate(
            View::factory(null, $data)
        );
    }

    protected function validate($html)
    {
        if (extension_loaded('tidy')) {
            $tidy = new \tidy();
            $conf = array(
                // PHP Bug: commenting out 'indent' (with true or false)
                // for some weird reason does chnage the Transfer-Encoding!
                'indent'			=> true,
                'tidy-mark'			=> false,
                'clean'				=> true,
                'output-xhtml'		=> false,
                'show-body-only'	=> true,
            );
            $tidy->parseString($html, $conf, 'UTF8');
            $tidy->cleanRepair();

            $html = $tidy->value; // with DOCTYPE
            #return $tidy->html()->value;
            #return tidy_get_output($tidy);
        }

        return $html;
    }

}

