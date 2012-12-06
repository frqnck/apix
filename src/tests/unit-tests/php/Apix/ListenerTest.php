<?php

namespace Apix;

class ListenerTest extends \PHPUnit_Framework_TestCase
{

    protected $listener;

     public function setUp()
    {
        $this->listener = new Listener;
        $this->observer = $this->getMock('Apix\Fixtures\ListenerMock', array('update'));

    }

    protected function tearDown()
    {
        unset($this->listener);
        unset($this->observer);
    }

    public function testUpdateIsCalledOnNotifyExactlyTwoTimes()
    {
        $this->observer->expects($this->exactly(2))
                 ->method('update');

        $this->listener->attach($this->observer);

        $this->listener->notify('123'); // one time
        $this->listener->notify('abc'); // two time
    }

    public function testSameWillAttachOnlyOnce()
    {
        $this->listener->attach($this->observer);
        $this->listener->attach($this->observer);
        $this->listener->attach($this->observer);

        $this->assertEquals(1, $this->listener->count());
    }

    public function testDifferentWillBeAccountable()
    {
        $this->listener->attach($this->observer);

        $obs2 = $this->getMock('Apix\Fixtures\ListenerMock', array('update'));
        $this->listener->attach($obs2);

        $this->assertEquals(2, $this->listener->count());
    }

    public function testDetachAndCountable()
    {
        $this->listener->attach($this->observer);

        $obs2 = $this->getMock('Apix\Fixtures\ListenerMock', array('update'));
        $this->listener->attach($obs2);

        $this->listener->detach($obs2);
        $this->assertEquals(1, $this->listener->count());
    }

    public function testGetIteratorIsEmptyArray()
    {
        $this->assertSame(array(), $this->listener->getIterator());
    }

    public function testGetIteratorOnAttach()
    {
        $this->listener->attach($this->observer);
        $this->assertTrue(count($this->listener->getIterator()) == 1);

        $obs2 = $this->getMock('Apix\Fixtures\ListenerMock');
        $this->listener->attach($obs2);
        $this->assertTrue(count($this->listener->getIterator()) == 2);
    }

    public function testGetListenersLevelReturnsConfig()
    {
        Config::getInstance()->setConfig(
            array(
                'listeners' => array(
                    'unit-test' => array(
                        'early' => 'but-not-late'
                    )
                )
            )
        );

        $plugins = $this->listener->getListenersLevel('unit-test');
        $this->assertSame('but-not-late', $plugins['early']);
    }

    public function testhook()
    {
        $plugins = array(
            'early' => array(
                'Apix\Fixtures\ListenerMock',
                'Apix\Fixtures\ListenerMock' => array('someVal1', 'someVal2')
            )
        );
        $this->listener->setListenersLevel('server', $plugins);

        $this->listener->hook('server', 'early');

        $this->assertSame(2, $this->listener->count());
    }

    /**
     * @expectedException   \BadMethodCallException
     */
    public function TODOtestThrowsExceptionWhenNotAvailable()
    {
        $plugins = array( 'early' => array('Whatever') );
        $this->listener->setListenersLevel('server', $plugins);

        $this->listener->hook('server', 'early');
    }

}