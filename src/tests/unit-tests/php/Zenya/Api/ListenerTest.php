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

   protected function implementExpectationsForMockObserver($mockObserver, $invokedCount)
    {
        $method = $mockObserver->expects($this->exactly($invokedCount))->method('update');
        if (0 < $invokedCount) {
            $method->with($this->isInstanceOf('SplSubject'));
        }
    }

    public function testUpdateIsCalledOnNotify()
    {
        $observer = $this->getMock('Zenya\Api\Listener\Mock', array('update'));
        #$this->implementExpectationsForMockObserver($observer, 2);
        $observer->expects($this->exactly(2))
                 ->method('update');

        $this->listener->attach($observer);
        $this->listener->notify();
        $this->listener->notify();
    }

    public function testAttachOnceAndCountable()
    {
        $observer = $this->getMock('Zenya\Api\Listener\Mock', array('update'));

        $this->listener->attach($observer);
        $this->listener->attach($observer);

        $this->assertEquals(1, $this->listener->count());
    }

    public function testDetachAndCountable()
    {
        $observer1 = $this->getMock('Zenya\Api\Listener\Mock', array('update'));
        $this->listener->attach($observer1);
        $this->listener->attach($observer1);

        $observer2 = $this->getMock('Zenya\Api\Listener\Mock', array('update'));
        $this->listener->attach($observer2);

        $this->assertEquals(2, $this->listener->count());

        $this->listener->detach($observer2);
        $this->assertEquals(1, $this->listener->count());
    }

    public function testGetIterator()
    {
        $this->assertSame(array(), $this->listener->getIterator());

        $observer1 = $this->getMock('Zenya\Api\Listener\Mock');
        $this->listener->attach($observer1);
        $this->assertTrue(count($this->listener->getIterator()) == 1);

        $observer2 = $this->getMock('Zenya\Api\Listener\Mock');
        $this->listener->attach($observer2);
        $this->assertTrue(count($this->listener->getIterator()) == 2);
    }

    /**
     * @covers Observer::update
     * @covers Subject::setValue
     * @covers Subject::getValue
     */
    public function OfftestUpdate()
    {
        $subject  = new Subject();
        $observer = new Observer();

        $subject->setValue('Observer Pattern');

        self::assertEquals($observer->update($subject), $subject->getValue());
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
