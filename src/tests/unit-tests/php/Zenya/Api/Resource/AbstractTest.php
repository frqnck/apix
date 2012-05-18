<?php

namespace Zenya\Api\Resource;

class ResourceAbstractTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers Zenya\Api\Resource::__construct
	 */
	public function testIsResourceAbstract()
	{
		$obj = new BlankResource('GET', array('paramName' => 'someValue'));
		$this->assertInstanceOf('Zenya\Api\Resource\ResourceAbstract', $obj);
	}

	
	/**
	 * @covers Zenya\Api\Resource::__construct
	 */
	public function testUpdateApiResource()
	{
		$obj = new BlankResource('PUT', array('optionalParam' => 'someValue'));
		$this->assertInstanceOf('Zenya\Api\Resource\BlankResource', $obj);
		$this->assertObjectHasAttribute('_output', $obj);
		$arr = $obj->toArray();
		$this->assertArrayHasKey('optionalParam', $arr['params']);
	}
	
	/**
	 * @covers Zenya\Api\Resource::__construct
	 * @expectedException			Zenya\Api\Resource\Exception
	 * @expectedExceptionMessage	Invalid resource's method (POST) specified.
	 * @expectedExceptionCode		405
	 */
	public function testThrows405Exception()
	{
		$obj = new BlankResource('POST', array('paramName' => 'someValue'));
		// TODO create assertHeader
		$this->assertHeader('Allow: GET, HEAD, OPTIONS');
	}

	/**
	 * @covers Zenya\Api\Resource::__construct
	 * @expectedException			Zenya\Api\Resource\Exception
	 * @expectedExceptionMessage	Required GET parameter "paramName" missing in action.
	 * @expectedExceptionCode		400
	 */
	public function testThrowsExceptionAndSet400Header()
	{
		$obj = new BlankResource('GET', array('xxx' => 'someValue'));
	}

	/**
	 * @covers Zenya\Api\Resource::__construct
	 */
	public function testRespondToOPTIONS()
	{
		$obj = new BlankResource('OPTIONS', array('xxx' => 'someValue'));
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
		// TODO create assertHeader
		$this->assertHeader('Allow: GET, HEAD, OPTIONS');
	}

	/**
	 * @covers Zenya\Api\Resource::__construct
	 */
	public function testRespondToHEAD()
	{
		$obj = new BlankResource('HEAD', array('xxx' => 'someValue'));
		// TODO create assertHeader
		#$this->assertHeader('Allow: GET, HEAD, OPTIONS');
		$this->markTestIncomplete(
				'This test has not been implemented yet.'
		);
	}

}