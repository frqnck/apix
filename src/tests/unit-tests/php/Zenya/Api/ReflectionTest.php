<?php
namespace Zenya\Api;

use Zenya\Api\Fixtures\DocbookClass;

class ReflectionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $reflected;

    protected function setUp()
    {
        $class = new DocbookClass;

        $this->reflected = new \ReflectionClass($class);
        $this->class = Reflection::parsePhpDoc($this->reflected->getDocComment());

        $this->method = Reflection::parsePhpDoc($this->reflected->getMethod('methodNameOne')->getDocComment());
    }

    protected function tearDown()
    {
        unset($this->reflected);
    }

    public function testClassDocsIsReturnedAsArray()
    {
        $this->assertInternalType('array', $this->class);
        $this->assertInternalType('array', $this->method);
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
       #$this->assertEquals('true', $this->class['api_public']);
       # $this->assertSame('1.0', $this->class['api_version']);
       # $this->assertSame('admin', $this->class['api_permission']);
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

    /*
        public function testGetDocsIsIncremental()
        {
            $method = $this->reflected->parseMethodDoc('methodNameOne');
            $method = $this->reflected->parseMethodDoc('methodNameTwo');
            $method = $this->reflected->parseMethodDoc('methodNameTwo');
            $docs = $this->reflected->getDocs();
            $this->assertSame(2, count($docs['methods']));
        }
     */

    public function testGetSourceOfTheWholeClass()
    {
        $src = Reflection::getSource($this->reflected);

        $this->assertRegExp('/^class Docbook/', $src, "Source should start by 'class ...'");
        $this->assertRegExp('/\s+}\n\n}$/', $src, "Source should end by '...}'");
    }

    public function testGetSourceOfMethod()
    {
        $src = Reflection::getSource($this->reflected->getMethod('methodNameOne'));

        $this->assertRegExp('/^\s+public function methodNameOne/', $src, "Source should start by 'public function methodNameOne'");
        $this->assertRegExp('/\s+}$/', $src, "Source should end by '...}'");
    }

    public function testSpecialCharacteres()
    {
        $this->markTestIncomplete('TODO: allow to use wildcard within doc (fix regex)');
        $this->assertSame('OPTIONS /*/etc...', $this->method['api_link']);
    }

    /* TODO Prefix handler */
    public function testPrefix()
    {
        $r = new Reflection('api_');
        $this->assertSame($r->getPrefix(), 'api_');
    }

}
