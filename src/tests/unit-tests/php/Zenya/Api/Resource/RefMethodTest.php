<?php
namespace Zenya\Api\Resource;

class FixtureTestClass {
    /**
     * Title
     *
     * Description 1st line
     * Description 2nd line
     *
     * @param int $namedInteger an integer
     * @param string $namedString a string
     * @param boolean $namedBoolean a boolean
     * @param array $optional something optional (here an array)
     * @return array result
     * @api_public true
	 * @api_version 1.0
	 * @api_permission admin
     * @api_randomName randomValue
     */
    public static function methodNameOne($namedInteger, $namedString, $namedBoolean, array $optional=null) {
        return array($namedInteger, $namedString, $namedBoolean, $optional);
    }
}

class RefMethodTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zenya_Api_Server
     */
    protected $method;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $className = 'Zenya\Api\Resource\FixtureTestClass';
        $methodName = 'methodNameOne';

        #$this->method = new RefMethod($className, $methodName, 'api_' );

        // usign decorator patern
        $method = new RefMethod($className, $methodName, 'api_' );
        $this->method = new RefDoc( $method );
    }

    protected function tearDown()
    {
        unset($this->method);
    }

    public function testMethodInstanceOfReflectionMethod()
    {
        $this->assertInstanceOf('ReflectionMethod', $this->method);
        $this->assertSame('methodNameOne', $this->method->getShortName());
    }

    public function testDocBookTitleAndDEscription()
    {
        $this->assertSame('Title', $this->method->getDoc('title'));
        $this->assertSame(
            'Description 1st line' . PHP_EOL .'Description 2nd line',
            $this->method->getDoc('description')
        );
    }

    public function testDocBookParam()
    {
        $params = $this->method->getDoc('params');
        $this->assertInternalType('array', $params);

        $this->assertSame(
            array(
                'type' => 'int',
                'name' => 'namedInteger',
                'description' => 'an integer',
            ),
            $params['namedInteger']
        );
    }

   public function testPrefixedParamsAsStrings()
    {
        $this->assertSame('true', $this->method->getDoc('api_public'));
        $this->assertSame('1.0', $this->method->getDoc('api_version'));
        $this->assertSame('admin', $this->method->getDoc('api_permission'));
        $this->assertSame('randomValue', $this->method->getDoc('api_randomName'));
	}

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Invalid element "notDefined"
     * @todo
    */
   public function testParamsThatAreNotDefinedThrowsException()
    {
        $this->method->getDoc('notDefined');
    }

}