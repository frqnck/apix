<?php
namespace Zenya\Api;

use Zenya\Api\Fixtures\DocbookClass;

//use Zenya\Api\Fixtures as Fixture;
//require_once APP_TESTDIR . '/Zenya/Api/Fixtures/DocbookClass.php';

class ReflectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $class, $method;

    protected function setUp()
    {
        $class = new DocbookClass;
        $methodName = 'methodNameOne';

        $this->reflected = new Reflection($class);
        $this->class = $this->reflected->parseClassDoc();

        $this->method = $this->reflected->parseMethodDoc($methodName);
    }

    protected function tearDown()
    {
        unset($this->class);
        unset($this->method);
    }

    public function testClassIsInstanceOfReflectionClass()
    {
        $class = $this->reflected;
        $this->assertInstanceOf('Zenya\Api\Reflection', $class);
        $this->assertSame('DocbookClass', $class->getShortName());
    }

    public function testOneMethodIsInstanceOfReflectionMethod()
    {
        $method = $this->reflected->getMethod('methodNameOne');
        $this->assertInstanceOf('ReflectionMethod',  $method);
        $this->assertSame('methodNameOne', $method->getShortName());
    }

    public function testClassDocBookTitleAndDescription()
    {
        $this->assertSame('Class title', $this->class['title']);

        $this->assertSame(
            'Class description 1st line' . PHP_EOL .'Class description 2nd line',
             $this->class['description']
        );
    }

    /* Not-too-use, just checking it actually work */
    public function testClassDocBookParamsIsAlwayaAnArray()
    {
        $params = $this->class['params'];
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

   public function testClassGroupsAsArray()
    {
        $grp = $this->class['randomGrouping'];
        $this->assertInternalType('array', $grp);
    }

   public function testClassPrefixedParamsAsStrings()
    {
        $this->assertEquals('true', $this->class['api_public']);
        $this->assertSame('1.0', $this->class['api_version']);
        $this->assertSame('admin', $this->class['api_permission']);
        $this->assertSame('classRandomValue', $this->class['api_randomName']);
    }

    public function testMethodDocBookTitleAndDescription()
    {
        $this->assertSame('Method one title', $this->method['title']);

        $this->assertSame(
            'Method description 1st line' . PHP_EOL .'Method description 2nd line',
            $this->method['description']
        );
    }

    public function testMethodDocBookParam()
    {
        $params = $this->method['params'];
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

    public function testMethodGroupsAsArray()
    {
        $grp = $this->method['rndGrouping'];
        $this->assertInternalType('array', $grp);
        $this->assertSame(array('a group value','another group value'), $grp);
    }

    public function testMethodPrefixedParamsAsStrings()
    {
        $this->assertSame('true', $this->method['api_public']);
        $this->assertSame('1.0', $this->method['api_version']);
        $this->assertSame('admin', $this->method['api_permission']);
        $this->assertSame('methodRandomValue', $this->method['api_randomName']);
    }

    public function testGetDocs()
    {
        $docs = $this->reflected->getDocs();
        $this->assertInternalType('array', $docs);
        $this->assertSame('Class title', $docs['title']);
        $this->assertSame(1, count($docs['methods']));
    }

    public function testGetDocsIsIncremental()
    {
        $method = $this->reflected->parseMethodDoc('methodNameOne');
        $method = $this->reflected->parseMethodDoc('methodNameTwo');
        $method = $this->reflected->parseMethodDoc('methodNameTwo');
        $docs = $this->reflected->getDocs();
        $this->assertSame(2, count($docs['methods']));
    }

    /**
     * @covers Zenya\Api\ReflectionClass::getActions
     */
    public function testGetActions()
    {
        $this->assertSame(
            array('PUT'=>'methodNameTwo', 'GET'=>'methodNameOne'),
            $this->reflected->getActions(
                array('PUT'=>'methodNameTwo', 'POST'=>'postMethod', 'GET'=>'methodNameOne')
            )
        );
    }

    public function testGetClassSource()
    {
        $src = $this->reflected->getSource();
        $this->assertTrue( preg_match('/^class Docbook/', $src) === 1, "Source should start by 'class ...'");
        $this->assertTrue( preg_match('/\s+}\n\n}$/', $src) === 1, "Source should end by '...}'");
    }

    public function testSpecialCharacteres()
    {
        $this->markTestIncomplete('TODO: maybe fix signle *s');
        $this->assertSame('OPTIONS /*/etc...', $this->method['api_link']);
    }

}
