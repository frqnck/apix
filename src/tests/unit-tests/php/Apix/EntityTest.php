<?php
namespace Apix;

use Apix\Entity,
    Apix\Router,
    Apix\Entity\EntityInterface;

class EntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $entity, $route;

    protected function setUp()
    {
        $this->entity = $this->getMock('Apix\Entity', array('underlineCall', '_parseDocs', 'getActions'));

        $this->route = $this->getMock('Apix\Router', array('getMethod'));
        $this->entity->setRoute($this->route);
    }

    protected function tearDown()
    {
        unset($this->route);
        unset($this->entity);
    }

    public function testAppend()
    {
        $this->entity->_append(array('redirect'=>'test'));

        $entity = $this->entity->toArray();
        $this->assertSame('test', $entity['redirect']);
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
                ->method('_parseDocs')
                ->will($this->returnValue($docs));

        $this->assertSame($docs, $this->entity->getDocs());
    }

    public function testGetDocsRetrievesTheSpecifiedApiDoc()
    {
        $docs = array('parseDocs', 'methods'=>array('GET'=>'doc for GET'));
        $this->entity->expects($this->once())
                ->method('_parseDocs')
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
        $this->route->server = new Server;
        $ref = new \ReflectionFunction(function(Request $request, Server $server, Response $response, Resources $resources){return 'something';});
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

        $request = New HttpRequest;
        $request->setHeader('CONTENT_TYPE', 'application/x-www-form-urlencoded');
        $request->setBody('param1=value1&param2=value2');

        $this->route->server = new Server(null, $request);

        $ref = new \ReflectionFunction(function(Request $request){echo '*test*'; return $request->getBodyData();return func_get_args();});
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

}