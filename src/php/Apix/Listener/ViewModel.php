<?php
namespace Apix\Listener;

class ViewModel extends AbstractListener
{

    /**
     * Constructor.
     *
     * @param array $options Array of options.
     */
    public function __construct(array $options=array())
    {
        #$this->options = $options+$this->options;
    }

    public function update(\SplSubject $server)
    {
        if ( $server->response->getFormat() !== 'html') {
            return;
        }


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