<?php
/**
 * Zenya API HTML Response
 *
 * e.g.
 * <Zenya_Model_Dummy generator="zend" version="1.0">
 * <foo>
 * <Zend_Version>1.10.8</Zend_Version>
 * <method><key_0>Zenya_Model_Dummy::foo</key_0></method>
 * <status>success</status>
 * </foo>
 * </Zenya_Model_Dummy>
 *
 * @category	Zenya
 * @package		Zenya_Api
 * @subpackage	Response
 * @copyright	Copyright (c) 2010 Info.com Ltd. (http://www.info.com)
 * @version		$Id$
 */

/** @see Zendya\Api\Response */
namespace Zenya\Api\Output;

use Zenya\Api\Output\Adapter;

class Html extends Adapter
{

    /**
     * Holds the media type for the output.
     * @var string
     * @see http://www.ietf.org/rfc/rfc2854.txt
     */
    public $contentType = 'text/html';

    /**
     * {@inheritdoc}
     */
    public function encode(array $data, $rootNode='root')
    {
        return $this->validate(
            $this->_recursivelyAppend(
                array($rootNode => $data)
            )
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
