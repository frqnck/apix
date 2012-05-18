<?php
namespace Zenya\Api\Listener;

class Mock implements \SplObserver
{
    /**
     * Last error message publicly readable
     * 
     * @var string
     */
    public $message;
    
	/**
     * Constructor.
     *
     * @param mixed $target Can be a file path (default: php://output), a resource,
     *                      or an instance of the PEAR Log class.
     * @param array $events Array of events to listen to (default: all events)
     *
     * @return void
     */
    public function __construct($target = 'php://filter/read=string.toupper/resource=dsa', array $events = array())
    {
		echo 'instantiated...';
	}
	
	
	
    /**
     * Observer
     * 
     * @param Pattern\Subject $errorHandler
     */
    public function update(\SplSubject $subject)
    {
		echo 'testing..';
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