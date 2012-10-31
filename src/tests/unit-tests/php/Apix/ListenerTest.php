<?php

namespace Apix;

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

    public function testUpdateIsCalledOnNotifyExactlyTwoTimes()
    {
        $obs = $this->getMock('Apix\Listener\Mock', array('update'));
        $obs->expects($this->exactly(2))
                 ->method('update');

        $this->listener->attach($obs);
        
        $this->listener->notify('123'); // one time
        $this->listener->notify('abc'); // two time
    }

    public function testSameWillAttachOnlyOnce()
    {
        $obs = $this->getMock('Apix\Listener\Mock', array('update'));

        $this->listener->attach($obs);
        $this->listener->attach($obs);
        $this->listener->attach($obs);

        $this->assertEquals(1, $this->listener->count());
    }

    public function testDifferentWillBeAccountable()
    {
        $obs1 = $this->getMock('Apix\Listener\Mock', array('update'));
        $this->listener->attach($obs1);

        $obs2 = $this->getMock('Apix\Listener\Mock', array('update'));
        $this->listener->attach($obs2);

        $this->assertEquals(2, $this->listener->count());
    }

    public function testDetachAndCountable()
    {
        $obs1 = $this->getMock('Apix\Listener\Mock', array('update'));
        $this->listener->attach($obs1);

        $obs2 = $this->getMock('Apix\Listener\Mock', array('update'));
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
        $obs1 = $this->getMock('Apix\Listener\Mock');
        $this->listener->attach($obs1);
        $this->assertTrue(count($this->listener->getIterator()) == 1);

        $obs2 = $this->getMock('Apix\Listener\Mock');
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

    public function testAddAllListeners()
    {
        $plugins = array(
            'early' => array(
                'Apix\Fixtures\ListenerMock',
                'Apix\Fixtures\ListenerMock' => array('someVal1', 'someVal2')
            )
        );
        $this->listener->setListenersLevel('server', $plugins);

        $this->listener->addAllListeners('server', 'early');

        $this->assertSame(2, $this->listener->count());
    }

    /**
     * @expectedException   \BadMethodCallException
     */
    public function TODOtestThrowsExceptionWhenNotAvailable()
    {
        $plugins = array( 'early' => array('Whatever') );
        $this->listener->setListenersLevel('server', $plugins);

        $this->listener->addAllListeners('server', 'early');
    }

}