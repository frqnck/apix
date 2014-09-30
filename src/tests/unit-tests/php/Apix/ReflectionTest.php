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

use Apix\Fixtures\DocbookClass;

class ReflectionTest extends TestCase
{

    /**
     * @var array
     */
    protected $reflected;

    protected function setUp()
    {
        $class = new DocbookClass();

        $this->reflected = new \ReflectionClass($class);

        $this->class = Reflection::parsePhpDoc(
            $this->reflected->getDocComment()
        );

        $this->method = Reflection::parsePhpDoc(
            $this->reflected->getMethod('methodNameOne'),
            array()
        );
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
            "Class description 1st line\nClass description 2nd line",
             $this->class['description']
        );
    }

    /* Not-in-use, just checking it actually work */
    public function testClassDocBookParamsIsAlwayaAnArray()
    {
        $params = $this->class['params'];
        $this->assertInternalType('array', $params);
        $this->assertSame(
            array(
                'type'          => 'int',
                'name'          => 'classNamedInteger',
                'description'   => 'an integer',
                'required'      => false
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
            "Method description 1st line\nMethod description 2nd line",
            $this->method['description']
        );
    }

    public function testMethodDocBookParam()
    {
        $params = $this->method['params'];

        $this->assertInternalType('array', $params);

        $this->assertSame(
            array(
                'type'          => 'int',
                'name'          => 'namedInteger',
                'description'   => 'an integer',
                'required'      => true
            ),
            $params['namedInteger']
        );
    }

    public function testMethodDocBookWithOptionalParam()
    {
        $params = $this->method['params'];

        $this->assertInternalType('array', $params);

        $this->assertSame(
            array(
                'type'          => 'array',
                'name'          => 'optional',
                'description'   => 'something optional (here an array)',
                'required'      => false
            ),
            $params['optional']
        );
    }

    public function testGetRequiredParams()
    {
        $requireds = Reflection::getRequiredParams(
            $this->reflected->getMethod('methodNameOne')
        );

        $this->assertSame(
            array('namedInteger','namedString','namedBoolean'),
            $requireds
        );
    }

    public function testMethodDocBookWithRequiredParam()
    {
        $method = Reflection::parsePhpDoc(
            $this->reflected->getMethod('methodNameTwo'),
            array('required')
        );

        $params = $method['params'];

        $this->assertInternalType('array', $params);

        $this->assertSame(
            array(
                'type'          => 'array',
                'name'          => 'required',
                'description'   => 'something required',
                'required'      => true
            ),
            $params['required']
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

        $this->assertRegExp('/^class Docbook/', $src,
            "Source should start by 'class ...'"
        );
        $this->assertRegExp('/\s+}\n\n}$/', $src,
            "Source should end by '...}'"
        );
    }

    public function testGetSourceOfMethod()
    {
        $src = Reflection::getSource(
            $this->reflected->getMethod('methodNameOne')
        );

        $this->assertRegExp('/^\s+public function methodNameOne/', $src,
            "Source should start by 'public function methodNameOne'"
        );
        $this->assertRegExp('/\s+}$/', $src, "Source should end by '...}'");
    }

    /* TODO Prefix handler */
    public function testPrefix()
    {
        $r = new Reflection('api_');
        $this->assertSame($r->getPrefix(), 'api_');
    }

    public function testWithWildcardCharacter()
    {
        $this->assertSame('OPTIONS .*\.foo\.bar', $this->method['api_link']);
    }

}
