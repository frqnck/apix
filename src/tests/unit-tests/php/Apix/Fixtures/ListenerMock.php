<?php
namespace Apix\Fixtures;

class ListenerMock implements \SplObserver
{

    public $value = null;
    public $integer = 0;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($value=null)
    {
        $this->value = $value;
        ++$this->integer;
    }

    /**
     * Observer
     *
     * @param Pattern\Subject $errorHandler
     */
    public function update(\SplSubject $subject)
    {
        return $subject->getNotice();
    }

}