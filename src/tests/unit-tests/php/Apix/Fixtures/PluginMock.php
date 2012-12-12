<?php
namespace Apix\Fixtures;

use Apix\Plugins\PluginAbstract;

class PluginMock extends PluginAbstract
{
    public static $hook = array('level', 'type');

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
        return $this->value;
        #return $subject->getNotice();
    }

}
