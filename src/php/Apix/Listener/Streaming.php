<?php
namespace Apix\Listener;

class Streaming extends AbstractListener
{

    public $streamed = false;
    public $callback = null;

    /**
     * Constructor.
     *
     * @param array $options Array of options.
     */
    public function __construct(array $options=array())
    {
        #$this->options = $options+$this->options;
    }

    static public function ob()
    {
        apache_setenv('no-gzip', '1');
        // ini_set('zlib.output_compression', 0);

        while (ob_get_level()) {
            ob_end_flush();
        }
        if (ob_get_length() === false) {
            ob_start();
        }
    }

    public function update(\SplSubject $response)
    {
        if ($this->streamed) {
            return;
        }

        $this->callback = function() use ($response)
        {
            header('Content-type: text/html; charset=utf-8');
            echo 'Begin ...<br>';

            for( $i = 0 ; $i < 10 ; $i++ )
            {
                echo $i . '<hr>';
                ob_flush();
                flush();
                sleep(1);
            }
            sleep(1);
            echo 'End ...<br />';
            exit;
        };

        if (!is_callable($this->callback)) {
            throw new \LogicException('The Response callback must be a valid PHP callable.');
        }

        // $this->headers->set('Cache-Control', 'no-cache');

        $this->streamed = true;
        call_user_func($this->callback);

        // $response->setOutput(
        //     $this->tidy(
        //         $response->getOutput(),
        //         isset($this->options[$format])
        //             ? $this->options[$format]
        //             : array()
        //     )
        // );
    }

}