<?php
namespace Apix\Plugin;

class Tidy extends PluginAbstract
{

    public static $hook = array('response', 'late');

    /**
     * @var array Options for tidy.
     * @see http://tidy.sourceforge.net/docs/quickref.html
     */
    protected $options = array(
        'enable'    => true,        // wether to enable or not
        'generic'   => array(
            // PHP Bug: commenting out 'indent' (with true or false)
            // for some weird reason does chnage the Transfer-Encoding!
            'indent'        => true,
            'indent-spaces' => 4,
            'wrap'          => 80,
            'clean'         => true
        ),
        'html'      => array(
            'tidy-mark'     => true,
        ),
        'xml'       => array(
            'input-xml'     => true,
            'output-xml'    => true,
        )
    );

    public function update(\SplSubject $response)
    {
        if (false === $this->options['enable']) {
            $this->log('Disabled');

            return false;
        }

        // @codeCoverageIgnoreStart
        if (!extension_loaded('tidy')) {
            $this->log('PHP extension not installed', null, 'DEBUG');

            return false;
        }
        // @codeCoverageIgnoreEnd

        $format = $response->getFormat();

        switch ($format) {

            case 'lst':
            case 'html':
            case 'xml':

                $response->setOutput(
                    $this->tidy(
                        $response->getOutput(),
                        isset($this->options[$format])
                            ? $this->options[$format]
                            : array()
                    )
                );

            default:
        }
    }

    /**
     * Tidy, sanitize the response output.
     *
     * @param  string $string
     * @param  array  $options
     * @return string
     */
    protected function tidy($string, array $options)
    {
        $tidy = new \tidy();
        $tidy->parseString($string, $options+$this->options['generic']);
        $tidy->cleanRepair();

        return $tidy->value;
    }

}
