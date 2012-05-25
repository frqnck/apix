<?php
namespace Zenya\Api\Resource;

class FixtureTestClass {
    /**
     * fixture test method
     *
     * Blahh blahh...
     *
     * see http://www.xmlrpc.com/validator1Docs
     *
     * @param int $int an integer
     * @param boolean $bool a boolean
     * @param string $string a string
     * @param float $double a float/double
     * @param mixed $datetime a datetime
     * @param mixed $base64 a base64 encoded string
     * @return array result
     * @api_public true
	 * @api_version 1.0
	 * @api_listener perm admin

	 *
     */
    public static function manyTypesTest($int, $bool, $string, $double, $datetime, $base64) {
        return array($int, $bool, $string, $double, $datetime, $base64);
    }
}

class MethodTest extends \PHPUnit_Framework_TestCase
{

   public function testConstructor()
    {
		$refClass = new \ReflectionClass('Zenya\Api\Resource\FixtureTestClass');

        $method = new Method( $refClass->getMethod('manyTypesTest'), 'api_' );
		
		print_r($method->version);
		print_r($method->perm);
		print_r($method->name);
		

        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
	}

    /**
     * @expectedException Zenya\Api\Exception
     * @expectedExceptionMessage Invalid rules array specified (not associative)
     * @expectedExceptionCode 500
     * @todo
    */
    public function testConstructorThrowsExceptionWhenNotAssociative()
    {
        #$route = new Router( array(1=>'/:controller/:action/:grab') );

        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
	}

}