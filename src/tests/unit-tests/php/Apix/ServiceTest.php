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

class ServiceTest extends TestCase
{
    public function testHasService()
    {
        $this->assertFalse( Service::has('foo') );

        Service::set('foo', 'bar');
        $this->assertTrue( Service::has('foo') );
    }

    public function testGetService()
    {
        Service::set('foo', 'bar');
        $this->assertSame( Service::get('foo'), 'bar' );
    }

}
