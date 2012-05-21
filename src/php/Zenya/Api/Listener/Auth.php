<?php
namespace Zenya\Api\Listener;

class Auth implements \SplObserver
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
	}

    public function update(\SplSubject $subject)
    {
		//print_r($subject);
        throw new Exception("Auth error", 401);
    }
    
}