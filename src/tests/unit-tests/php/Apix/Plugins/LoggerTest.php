<?php
namespace Apix\Plugins;

use Apix\TestCase;

class LoggerTest extends TestCase
{
    protected $logger, $adapter;

    public function setUp()
    {
        $this->adapter = $this->getMock('Apix\Plugins\Log\Adapter');
        $this->logger = new Logger(
            array('enable'=>true, 'adapter'=>$this->adapter)
        );
    }

    protected function tearDown()
    {
        unset($this->logger);
        unset($this->adapter);
    }

    public function OFFtestItWillSkipWhenDisable()
    {
        $log = new Logger($this->adapter, array('enable'=>false));
        $this->assertFalse(
            $log->update( 'something' )
        );
    }

    public function logProvider()
    {
        return array(
            array('+1minute', 60),
            array('1min', 60),
            array('100sec', 100),
            array('+1minute', 60),
            array('2days', 172800),
            array('48hours', 172800),
            array('1week', 604800)
        );
    }

    /**
     * @dataProvider logProvider
     */
    public function testLog($msg, $level, $ctx=null)
    {
        $this->assertTrue(
            $this->logger->log($msg, $level, $ctx)
        );
    }

}
