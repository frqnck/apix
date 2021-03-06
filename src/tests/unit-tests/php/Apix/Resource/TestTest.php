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

namespace Apix\Resource;

use Apix\Fixtures\BlankResource;

class TestTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Apix\Resource::__construct
     * @expectedException			\InvalidArgumentException
     * @expectedExceptionMessage	Invalid resource's method (POST) specified.
     * @expectedExceptionCode		405
     */
    public function testThrows405Exception()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $obj = new BlankResource('POST', array('paramName' => 'someValue'));

        // TODO create assertHeader
        $this->assertHeader('Allow: GET, HEAD, OPTIONS');
    }

    /**
     * @covers Apix\Resource::__construct
     * @expectedException			BadMethodCallException
     * @expectedExceptionMessage	Required GET parameter "paramName" missing in action.
     * @expectedExceptionCode		400
     */
    public function testThrowsExceptionAndSet400Header()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $obj = new BlankResource('GET', array('xxx' => 'someValue'));
    }

    /**
     * @covers Apix\Resource::__construct
     */
    public function testRespondToOPTIONS()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $obj = new BlankResource('OPTIONS', array('xxx' => 'someValue'));
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
        // TODO create assertHeader
        $this->assertHeader('Allow: GET, HEAD, OPTIONS');
    }

    /**
     * @covers Apix\Resource::__construct
     */
    public function testRespondToHEAD()
    {

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $obj = new BlankResource('HEAD', array('xxx' => 'someValue'));
        // TODO create assertHeader
        #$this->assertHeader('Allow: GET, HEAD, OPTIONS');
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}
