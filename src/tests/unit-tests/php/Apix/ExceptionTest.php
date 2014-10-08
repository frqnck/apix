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

    public function testStartupException()
    {
        // trigger_error("boo!", E_CORE_ERROR);
        Exception::startupException(new Exception());
        $this->expectOutputString('<h1>500 Internal Server Error</h1>');
    }

}
