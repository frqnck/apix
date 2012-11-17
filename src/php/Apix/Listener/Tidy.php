<?php
namespace Apix\Listener;

class Tidy extends AbstractListener
{

    /**
     * @see http://tidy.sourceforge.net/docs/quickref.html
     * @var array
     */
    protected $options = array(
        'enable'    => true,        // Wether to enable output tidying at all.

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

    /**
     * Constructor.
     *
     * @param Cache\Adapter $adapter
     * @param array $options Array of options.
     */
    public function __construct(array $options=array())
    {
        $this->options = $options+$this->options;
    }

    public function update(\SplSubject $response)
    {
        if ( false === $this->options['enable'] ){
            $this->log('Disabled');
            return false;
        }

        // @codeCoverageIgnoreStart
        if (!extension_loaded('tidy')) {
            $this->log('The Tidy extension is not available.');
            return false;
        }
        // @codeCoverageIgnoreEnd

        $format = $response->getFormat();

        switch($format) {

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
     * @param  array $options
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