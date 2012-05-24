<?php
namespace Zenya\Api\Listener;

class Mock implements \SplObserver
{

    /**
     * Constructor.
     *
     * @param mixed $target Can be a file path (default: php://output), a resource,
     *                      or an instance of the PEAR Log class.
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     */
    public function __construct()
    {
        echo 'instantiated...' . PHP_EOL;
    }

    /**
     * Observer
     *
     * @param Pattern\Subject $errorHandler
     */
    public function update(\SplSubject $subject)
    {
        $notice = $subject->getNotice();
        echo '*** Notice update:'  . $notice['name'] . ' / obj: ' . $subject->stage;
        echo xdebug_time_index(), "\n";

        #print_r($subject);
    }

    /**
     * Observer
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("class %s contains error '%s'", __CLASS__, '$this->show()');
    }
}
