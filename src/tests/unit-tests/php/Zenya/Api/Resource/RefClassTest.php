<?php
namespace Zenya\Api\Resource;

//Use Zenya\Api\Resource as Fixture;

//require_once '../fixture/DoockbookClassFixture.php';

/**
 * Class title
 *
 * Class description 1st line
 * Class description 2nd line
 *
 * @param int $classNamedInteger an integer
 * @param string $classNamedString a string
 * @param boolean $classNamedBoolean a boolean
 * @param array $classOptional something optional (here an array)
 * @api_public true
 * @api_version 1.0
 * @api_permission admin
 * @api_randomName classRandomValue
 */
class DoockbookClassFixture {

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
    public static function methodNameOne($namedInteger, $namedString, $namedBoolean, array $optional=null)
    {
        return array($namedInteger, $namedString, $namedBoolean, $optional);
    }
}

class RefClassTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Zenya\Api\Resource\Class
     */
    protected $class;

    protected function setUp()
    {
        $className = 'Zenya\Api\Resource\DoockbookClassFixture';
        $methodName = 'methodNameOne';

        $refClass = new \ReflectionClass($className);
        $this->class = new RefDoc($refClass);
    }

    protected function tearDown()
    {
        unset($this->class);
    }

    public function testClassIsInstanceOfReflectionClass()
    {
        $this->assertInstanceOf('Zenya\Api\Resource\RefDoc', $this->class);
        #$this->assertSame('DoockbookClassFixture', $this->class->getShortName());
    }

    public function testClassDocBookTitleAndDescription()
    {
        //print_r($this->class);exit;

        $this->assertSame('Class title', $this->class->getDoc('title'));

        $this->assertSame(
            'Class description 1st line' . PHP_EOL .'Class description 2nd line',
             $this->class->getDoc('description')
        );
    }

    public function testClassDocBookParam()
    {
        $params = $this->class->getDoc('params');
        $this->assertInternalType('array', $params);

        $this->assertSame(
            array(
                'type' => 'int',
                'name' => 'classNamedInteger',
                'description' => 'an integer',
            ),
            $params['classNamedInteger']
        );
    }

   public function testClassPrefixedParamsAsStrings()
    {
        $this->assertEquals('true', $this->class->getDoc('api_public'));
        $this->assertSame('1.0', $this->class->getDoc('api_version'));
        $this->assertSame('admin', $this->class->getDoc('api_permission'));
        $this->assertSame('classRandomValue', $this->class->getDoc('api_randomName'));
	}

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Invalid element "notDefined"
     * @todo
    */
   public function testClassParamsThatAreNotDefinedThrowsException()
    {
        $this->class->getDoc('notDefined');
    }

}