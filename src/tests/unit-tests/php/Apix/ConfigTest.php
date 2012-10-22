<?php

namespace Apix;

#use Pimple;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    public function setUp()
    {
        $this->config = new Config;
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

    public function testConstructorLoadsDistFile()
    {
        $config = new Config;
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

    public function testRetrieveMany()
    {
        $default = $this->config->get('resources')+$this->config->getDefault('resources');

        $this->assertSame($default, $this->config->retrieve('resources'));
    }

    public function testRetrieveOne()
    {
        $default = $this->config->getDefault('resources');
        $this->assertSame($default['OPTIONS'], $this->config->retrieve('resources', 'OPTIONS'));
    }

    /**
     * @expectedException   RuntimeException
     */
    public function testRetrieveThrowRuntimeException()
    {
        $default = $this->config->getDefault('resources');
        $this->config->retrieve('resources', 'not-existant');
    }

    public function testGetManyResources()
    {
        $default = $this->config->get('resources')+$this->config->getDefault('resources');

        $this->assertSame($default, $this->config->getResources());
    }

    public function testGetManyPlugins()
    {
        $default = $this->config->get('listeners')+$this->config->getDefault('listeners');

        $this->assertSame($default, $this->config->getListeners());
    }

    public function testGetManyServices()
    {
        $default = $this->config->get('services');

        $this->assertSame($default, $this->config->getServices());
    }

    public function testGetOneService()
    {
        $this->assertInternalType('array', $this->config->getServices('users'));

        // $this->assertSame('$default', $this->config->getServices('users'));

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
     * TEMP: testIsSingletonIsNotClonable
     * @covers Apix\Config::__clone
     */
    public function testIsSingletonIsNotClonable()
    {
        // $r = clone $this->request;
        $r = new \ReflectionClass($this->config);
        $p = $r->getMethods(\ReflectionMethod::IS_PRIVATE|\ReflectionMethod::IS_FINAL);
        $this->assertSame('__clone', $p[0]->name);
    }

    /**
     * TEMP: testGetAndSetInject
     */
    public function testGetAndSetInject()
    {
        $this->config->inject('some_key', 'some_value');

        $this->assertSame('some_value', $this->config->getInjected('some_key'));
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
