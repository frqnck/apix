<?php
namespace Zenya\Api\Listener;

class Mock implements \SplObserver
{

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Observer
     *
     * @param Pattern\Subject $errorHandler
     */
    public function update(\SplSubject $subject)
    {
        #print_r($subject);

        $notice = $subject->getNotice();
        echo '*** Notice update:'  . $notice['name'] . ' / obj: ';
        // . $subject->stage;
        echo xdebug_time_index(), "\n";
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
