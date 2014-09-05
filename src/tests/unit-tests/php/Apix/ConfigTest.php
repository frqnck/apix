<?php

namespace Apix;

#use Pimple;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    public function setUp()
    {
        $this->config = new Config();
    }

    protected function tearDown()
    {
        unset($this->config);
    }

    public function testConstructorSetFromArray()
    {
        $config = new Config(array('unit_test' => __FUNCTION__));
        $this->assertEquals(__FUNCTION__, $config->get('unit_test'));
    }

    public function testConstructorSetFromString()
    {
        $file = APP_TESTDIR . '/Apix/Fixtures/config.unit-test.php';
        $config = new Config($file);
        $this->assertEquals($file, $config->get('config_path'));
    }

    public function testConstructorLoadsDistFileByDefault()
    {
        $config = new Config();
        $this->assertRegExp('@\/.*$@', $config->get('config_path'));
    }

    public function testConstructorMergeDistFile()
    {
        $config = new Config(array('api_version'=>'user-defined'));
        $this->assertRegExp('@\/.*$@', $config->get('config_path'));
    }

    /**
     * @expectedException       \RuntimeException
     * @expectedExceptionCode   5000
     */
    public function testSetConfigFromFileThrowException()
    {
        $file = 'file-that-do-not-exist';
        $this->config->getConfigFromFile($file);
    }

    /**
     * @expectedException       \RuntimeException
     * @expectedExceptionCode   5001
     */
    public function testSetConfigFromFileNotReturnedThrowException5001()
    {
        $file = APP_TESTDIR . '/Apix/Fixtures/config-not-returned.unit-test.php';
        $this->config->getConfigFromFile($file);
    }

    /**
     * @expectedException   InvalidArgumentException
     */
    public function testInexistantThrowsInvalidArgumentException()
    {
        $this->config->get('non-existant');
    }

    /**
     * @covers Apix\Config::getConfig
     * @covers Apix\Config::get
     */
    public function testGetConfigHasDefaultResource()
    {
        $r = $this->config->getConfig();
        $this->assertArrayHasKey('resources', $r['default']);

        $r = $this->config->get();
        $this->assertArrayHasKey('resources', $r['default']);
    }

    public function testGetDefaultWithNull()
    {
        $r = $this->config->getConfig();
        $default = $this->config->getDefault();
        $this->assertSame($r['default'], $default);
    }

    /**
     * @expectedException   InvalidArgumentException
     */
    public function testGetDefaultThrowsAnInvalidArgumentException()
    {
        $default = $this->config->getDefault('wrong');
    }

    public function testRetrieveMany()
    {
        $defaults = $this->config->get('resources');
        $defaults += $this->config->getDefault('resources');

        $this->assertSame(
            $defaults,
            $this->config->retrieve('resources')
        );
    }

    public function testRetrieveOne()
    {
        $defaults = $this->config->getDefault('resources');
        $this->assertSame(
            $defaults['OPTIONS'],
            $this->config->retrieve('resources', 'OPTIONS')
        );
    }

    /**
     * @expectedException   RuntimeException
     */
    public function testRetrieveThrowRuntimeException()
    {
        $this->config->retrieve('resources', 'not-existant');
    }

    public function testGetManyResources()
    {
        $defaults = $this->config->get('resources');
        $defaults += $this->config->getDefault('resources');

        $this->assertSame($defaults, $this->config->getResources());
    }

    public function testGetManyServices()
    {
        $default = $this->config->get('services');

        $this->assertSame($default, $this->config->getServices());
    }

    public function testSetGetOneService()
    {
        $this->config->setService('foo', 'bar');
        $this->assertSame('bar', $this->config->getServices('foo'));
    }

    public function testAddValue()
    {
        $this->config->add('foo', 'bar');
        $this->assertSame(array('bar'), $this->config->get('foo'));
    }


    /**
     * TEMP: testIsSingleton
     */
    public function testIsSingleton()
    {
        $conf = Config::getInstance();
        $conf2 = Config::getInstance();

        $this->assertSame($conf, $conf2);
    }

    /**
     * TEMP: testIsSingletonAndNotClonable
     * @covers Apix\Config::__clone
     */
    public function testIsSingletonAndNotClonable()
    {
        // $r = clone $this->request;
        $r = new \ReflectionClass($this->config);
        $p = $r->getMethods(\ReflectionMethod::IS_PRIVATE|\ReflectionMethod::IS_FINAL);
        $this->assertSame('__clone', $p[0]->name);
    }

    /**
     * TEMP: testGetAndSetInject
     */
    public function testConfigInject()
    {
        $this->config->inject('some_key', 'some_value');

        $this->assertSame('some_value', $this->config->getInjected('some_key'));
    }

    /**
     * TEMP: testGetAndSetInject
     */
    public function testSetItem()
    {
       $c = Config::getInstance();
       $c->set('plugins', array());
        $this->assertEmpty($c->get('plugins'));
    }

    public function TODO_testEmptyConfigSetAnAssociativeArrayOfEmptyArray()
    {
        $this->config->setConfig(
            array() // make sure 'config.dist.php', etc.. don't get loaded here.
        );
        $this->assertEquals(array(), $this->config->get('resources'));
        $this->assertEquals(array(), $this->config->get('services'));
        $this->assertEquals(array(), $this->config->get('listeners'));
    }

}
