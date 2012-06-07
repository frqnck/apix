<?php
class HeaderTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        ob_start(); // <-- very important!
    }

    protected function tearDown()
    {
        header_remove(); // <-- VERY important.
        parent::tearDown();
    }

    public function testProperHeaderSet()
    {
        header('Location: foo');
        $headers_list = headers_list();
        $this->assertNotEmpty($headers_list);
        $this->assertContains('Location: foo', $headers_list);
    }

}