<?php

namespace Zenya\Api;

class ListenerTest extends \PHPUnit_Framework_TestCase
{

    protected $listener;

    public function setUp()
    {
        $this->listener = new Listener;
    }

    protected function tearDown()
    {
        unset($this->listener);
    }

    public function testObserversAreUpdated()
    {

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
        // Create a mock for the Observer class,
        // only mock the update() method.
        $observer = new Listener\Mock;
/*
        $observer = $this->getMock('Observer', array('update'));
        $observer->expects($this->once())
                 ->method('update')
                 ->with($this->equalTo('something'));
 */
        $this->listener->attach($observer);
 
        // Call the doSomething() method on the $subject object
        // which we expect to call the mocked Observer object's
        // update() method with the string 'something'.
        $this->listener->notify();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetNonExistentProvider()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}