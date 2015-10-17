<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix;

class ExceptionTest extends TestCase
{

    protected function setUp()
    {
        $this->logger = new Log\Logger\Runtime();
        Service::set('logger', $this->logger);

        set_error_handler(array('Apix\Exception', 'errorHandler'), E_ALL);
    }

    /**
     * @expectedException           \ErrorException
     * @expectedExceptionCode       500
     */
    public function testErrorHandlerThrowsErrorExceptionTriggered()
    {
        trigger_error('boo!', E_USER_WARNING);
    }

    /**
     * @expectedException           \ErrorException
     * @expectedExceptionCode       500
     */
    public function testErrorHandlerThrowsErrorExceptionReal()
    {
        $response = json_decode();
    }

    /**
     * @expectedException           \ErrorException
     * @expectedExceptionCode       500
     */
    public function testErrorHandlerThrowsErrorExceptionCalled()
    {
        Exception::errorHandler(0);
    }

    /**
     * @expectedException           \ErrorException
     * @expectedExceptionCode       500
     * @expectedExceptionMessage    boo!
     */
    public function testErrorHandlerThrowsRecoverableErrorException()
    {
        // trigger_error("boo!", \E_USER_ERROR);
        Exception::errorHandler(4096, 'boo!');
    }
    
    public function testExceptionToArray()
    {
        $r = Exception::toArray(
            new Exception('some msg', 123, new Exception('prev'))
        );

        $this->assertSame('some msg', $r['message']);
        $this->assertSame(123, $r['code']);
        $this->assertSame('Apix\Exception', $r['type']);
        $this->assertArrayHasKey('trace', $r);
    }

    public function testStartupException()
    {
        $str = Exception::CRITICAL_ERROR_STRING;

        $res = Exception::startupException( new Exception() );
        $this->expectOutputString('<h1>' . $str . '</h1>');
       
        $this->assertArrayHasKey('msg', $res);
        $this->assertArrayHasKey('ctx', $res);

        $items = $this->logger->getItems();

        $this->assertRegExp(
            '@^\[.*\] CRITICAL ' . $str . ' - #0 Startup.*:\d+$@s',
            $items[0]
        );
    }

    /**
     * @expectedException           \ErrorException
     * @_expectedExceptionCode       500
     * @_expectedExceptionMessage    boo!
     */
    // public function testRegressionGitHub5()
    // {
    //     Exception::errorHandler(4096, 'boo!');

    //     // throw new Exception("an oops occurred");
    // }

}
