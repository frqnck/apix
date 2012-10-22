<?php
namespace Apix\Listener;

class Log implements \SplObserver
{

    /**
     * The log target, it can be a a resource or a PEAR Log instance.
     *
     * @var resource|Log $target
     */
    protected $target = null;

    /**
     * The events to log.
     *
     * @var array $events
     */
    public $notices = array(
        'late',
        'connect',
        'sentHeaders',
        'sentBody',
        'receivedHeaders',
        'receivedBody',
        'disconnect',
    );

    /**
     * Constructor.
     *
     * @param mixed $target Can be a file path (default: php://output), a resource,
     *                      or an instance of the PEAR Log class.
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     */
    public function __construct($target = 'php://output', array $events = array())
    {
        if (!empty($events)) {
            $this->events = $events;
        }
        if (is_resource($target) || $target instanceof Log) {
            // use Pear::Log
            $this->target = $target;
        } elseif (false === ($this->target = @fopen($target, 'ab'))) {
            throw new Exception("Unable to open '{$target}'", 500);
        }
    }

    /**
     * Called when the request notifies us of an event.
     *
     * @param HTTP_Request2 $subject The HTTP_Request2 instance
     *
     * @return void
     */
    public function update(\SplSubject $subject)
    {
        $notice = $subject->getNotice();

        #$data = $subject->response;

        $this->log("*Log: ${notice['name']}, ${notice['data']}");

        #$this->log('* Connected to ' . $notice['name']);

return;

        if (!in_array($notice['name'], $this->notices)) {
            return;
        }

        switch ($notice['name']) {
        case 'late':

            break;

        case 'connect':
            $this->log('* Connected to ' . $event['data']);
            break;
        case 'sentHeaders':
            $headers = explode("\r\n", $event['data']);
            array_pop($headers);
            foreach ($headers as $header) {
                $this->log('> ' . $header);
            }
            break;
        case 'sentBody':
            $this->log('> ' . $event['data'] . ' byte(s) sent');
            break;
        case 'receivedHeaders':
            $this->log(sprintf(
                '< HTTP/%s %s %s', $event['data']->getVersion(),
                $event['data']->getStatus(), $event['data']->getReasonPhrase()
            ));
            $headers = $event['data']->getHeader();
            foreach ($headers as $key => $val) {
                $this->log('< ' . $key . ': ' . $val);
            }
            $this->log('< ');
            break;
        case 'receivedBody':
            $this->log($event['data']->getBody());
            break;
        case 'disconnect':
            $this->log('* Disconnected');
            break;
        }
    }

    /**
     * Logs the given message to the configured target.
     *
     * @param string $message Message to display
     *
     * @return void
     */
    protected function log($str)
    {
        if ($this->target instanceof Log) {
            $this->target->debug($str);
        } elseif (is_resource($this->target)) {
            fwrite($this->target, $str . "\r\n");
        }
    }

}
