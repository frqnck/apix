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

use Apix\Entity,
    Apix\Router,
    Apix\Entity\EntityInterface;

class EntityTest extends TestCase
{

    /**
     * @var array
     */
    protected $entity, $route;

    protected function setUp()
    {
        $this->entity = $this->getMock('Apix\Entity',
            array('underlineCall', 'parseDocs', 'getActions')
        );

        $this->route = $this->getMock('Apix\Router', array('getMethod'));
        $this->entity->setRoute($this->route);
    }

    protected function tearDown()
    {
        unset($this->route, $this->entity);
    }

    public function testAppend()
    {
        $this->entity->_append(array('redirect'=>'test'));

        $entity = $this->entity->toArray();
        $this->assertSame('test', $entity['redirect']);
        $this->assertSame('test', $this->entity->getRedirect());
    }

    public function testCall()
    {
        $this->entity->expects($this->once())
                ->method('underlineCall')
                ->will($this->returnValue(array('calledData')));

        $this->assertEquals(array('calledData'), $this->entity->call(), "Checking mocking actually work.");
    }

    public function testGetDocsRetrievesAllTheApiDocs()
    {
        $docs = array('parseDocs', 'methods'=>array('GET'=>'doc for GET'));
        $this->entity->expects($this->once())
                ->method('parseDocs')
                ->will($this->returnValue($docs));

        $this->assertSame($docs, $this->entity->getDocs());
    }

    public function testGetDocsRetrievesAllTheApiDocsWithInternalCache()
    {
        $this->skipIfMissing('apc');

        // if (!ini_get('apc.enable_cli')) {
        //     self::markTestSkipped(
        //         'apc.enable_cli MUST be enabled in order to run this unit test'
        //     );
        // }

        $config = \Apix\Config::getInstance();
        $config->set('cache_annotation', true);

        $this->testGetDocsRetrievesAllTheApiDocs();
    }

    public function testGetDocsRetrievesTheSpecifiedApiDoc()
    {
        $docs = array('parseDocs', 'methods'=>array('GET'=>'doc for GET'));
        $this->entity->expects($this->any())
                ->method('parseDocs')
                ->will($this->returnValue($docs));

        $this->assertSame($docs['methods']['GET'], $this->entity->getDocs('GET'));
    }

    public function testHasMethod()
    {
        $this->entity->expects($this->any())
                ->method('getActions')
                ->will($this->returnValue(array('POST'=>'onCreate')));

       $this->assertFalse($this->entity->hasMethod('not-existant'));
       $this->assertTrue($this->entity->hasMethod('POST'));
    }

    public function testGetDefaultAction()
    {
       $this->assertSame('help', $this->entity->getDefaultAction('OPTIONS'));
       $this->assertNull($this->entity->getDefaultAction('inexistant'));
   }

   // public function testSetActions()
   //  {
   //      $r = new \ReflectionObject($this->entity);
   //      $prop1 = $r->getProperty('actions');
   //      $prop1->setAccessible(true);

   //      $this->entity->setActions( array('key'=>'val') );

   //      $this->assertSame(
   //          $prop1->getValue($this->entity),
   //          $this->entity->getAllActions()
   //      );

   //  }

    /**
     * @dataProvider actionProvider
     */
   public function testGetAllActions(array $actions, array $expected)
    {
        $this->entity->expects($this->any())
                ->method('getActions')
                ->will($this->returnValue($actions));

        $this->assertSame(
            $expected,
            $this->entity->getAllActions()
        );
    }

    public function actionProvider()
    {
        return array(
            'Without GET Has No HEAD' => array( array(), array('OPTIONS' => 'help')),
            'With GET has HEAD' => array(array('GET'=>'some'), array('GET'=>'some', 'OPTIONS' => 'help', 'HEAD' => 'test') ),
            'HAs POST but without GET has no HEAD' => array(array('POST'=>'some'), array('POST'=>'some', 'OPTIONS' => 'help') ),
        );
    }

    /**
     * @expectedException           \BadMethodCallException
     * @expectedExceptionCode       400
     */
    public function testGetValidatedParams()
    {
        $refClass = new \ReflectionClass('Apix\Fixtures\DocbookClass');
        $refMethod = $refClass->getMethod('methodNameTwo');

        $params = $this->entity->getValidatedParams($refMethod, 'aStringName', array('arg1'=>123));
        $this->assertSame(array('arg1'=>123), $params);

        $params = $this->entity->getValidatedParams($refMethod, 'aStringName', array('arg2'=>345));
    }

    public function testAutoInject()
    {
        $this->route->server = new Server();
        $ref = new \ReflectionFunction(function (Request $request, Server $server, Response $response, Resources $resources) {return 'something';});
        $params = $this->entity->getValidatedParams($ref, 'aStringName', array());

        $this->assertInstanceOf('Apix\Request', $params['request']);
        $this->assertInstanceOf('Apix\Server', $params['server']);
        $this->assertInstanceOf('Apix\Response', $params['response']);
        $this->assertInstanceOf('Apix\Resources', $params['resources']);
    }

    public function testAutoInjectHttpRequestInsteadOfRequest()
    {
        // hack to imitate a web browser
        $_REQUEST['param1'] = 'value1';
        $_REQUEST['param2'] = 'value2';

        $request = new HttpRequest();
        $request->setHeader('CONTENT_TYPE', 'application/x-www-form-urlencoded');
        $request->setBody('param1=value1&param2=value2');

        $this->route->server = new Server(null, $request);

        $ref = new \ReflectionFunction(function (Request $request) {echo '*test*'; return $request->getBodyData();return func_get_args();});
        $params = $this->entity->getValidatedParams($ref, 'aStringName', array());

        $this->assertInstanceOf('Apix\HttpRequest', $params['request']);
        $this->assertEquals(array('param1'=>'value1', 'param2'=>'value2'), $params['request']->getBodyData());
    }

    public function testSetGetRoute()
    {
        $this->entity->setRoute($this->route);
        $this->assertAttributeEquals($this->route, 'route', $this->entity);

        $this->assertEquals($this->route, $this->entity->getRoute());
    }

    public function testGetHasRedirect()
    {
        $this->assertFalse($this->entity->hasRedirect());
        $this->entity->_append(array('redirect'=>'route-name-to-redirect-to'));
        $this->assertTrue($this->entity->hasRedirect());
        $this->assertSame('route-name-to-redirect-to', $this->entity->getRedirect());
    }

    public function testGetAnnotationValue()
    {
        $this->assertNull($this->entity->getAnnotationValue('not-exisiting'));

        $this->entity = $this->getMock('Apix\Entity', array('getDocs'));
        $this->entity->setRoute($this->route);
        $this->entity->expects($this->any())
                ->method('getDocs')
                ->will($this->onConsecutiveCalls(
                    array('api_role' => 'admin'),
                    array('api_role' => 'public'),
                    array('api_auth_role' => null)
                ));

        $this->assertSame('admin', $this->entity->getAnnotationValue('api_role'));
        $this->assertSame('public', $this->entity->getAnnotationValue('api_role'));
        $this->assertNull($this->entity->getAnnotationValue('api_auth_role'));
    }

    public function testConvertToArray()
    {
        $results = array(
            'array' => array('foo', 'bar'),
            'string' => "this is a string",
            'object' => new \stdClass,
        );
        $results['object']->foo = 'bar';

        foreach($results as $result) {
             $this->assertInternalType('array', Entity::convertToArray($result));
        }
    }

    public function testConvertToArrayWithNestedArrayAndObjects()
    {
        $object = new \stdClass;
        $object->foo = 'bar';
        $object->ray = array('ban');
        $object->nested = new \stdClass;
        $object->nested->biz = 'yo';

        $array = Entity::convertToArray($object);

        $this->assertInternalType('array', $array);
        $this->assertEquals($object->foo, $array['foo']);

        $this->assertEquals($object->ray, $array['ray']);
        $this->assertEquals($object->nested, $array['nested']);

        $this->markTestIncomplete('TODO: convert nested objects recursively...');

        $this->assertEquals($object->nested->biz, $array['nested']['biz']);
    }

}
