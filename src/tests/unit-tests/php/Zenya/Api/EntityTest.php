<?php
namespace Zenya\Api;

use Zenya\Api\Entity,
    Zenya\Api\Router,
    Zenya\Api\Entity\EntityInterface;

class EntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $entity, $route;

    protected function setUp()
    {
        $this->entity = $this->getMock('Zenya\Api\Entity', array('underlineCall', '_parseDocs', 'getActions'));
        $this->route = $this->getMock('Zenya\Api\Router', array('getMethod'));
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

    public function testGetDocs()
    {
        $docs = array('parseDocs', 'methods'=>array('GET'=>'doc for GET'));
        $this->entity->expects($this->once())
                ->method('_parseDocs')
                ->will($this->returnValue($docs));

        $this->assertSame($docs, $this->entity->getDocs());
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
    public function testGetRequiredParams()
    {
        $refClass = new \ReflectionClass('Zenya\Api\Fixtures\DocbookClass');
        $refMethod = $refClass->getMethod('methodNameTwo');

        $params = $this->entity->getRequiredParams($refMethod, 'aStringName', array('arg1'=>123));
        $this->assertSame(array('arg1'=>123), $params);

        $params = $this->entity->getRequiredParams($refMethod, 'aStringName', array('arg2'=>345));
    }

    public function testAutoInject()
    {
        $this->route->server = new Server;
        $ref = new \ReflectionFunction(function(Request $request, Server $server, Response $response, Resources $resources){return 'something';});
        $params = $this->entity->getRequiredParams($ref, 'aStringName', array());

        $this->assertInstanceOf('Zenya\Api\Request', $params['request']);
        $this->assertInstanceOf('Zenya\Api\Server', $params['server']);
        $this->assertInstanceOf('Zenya\Api\Response', $params['response']);
        $this->assertInstanceOf('Zenya\Api\Resources', $params['resources']);
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

    public function testIsPublic()
    {
        $this->assertTrue($this->entity->isPublic());

        $this->entity = $this->getMock('Zenya\Api\Entity', array('getDocs'));
        $this->entity->setRoute($this->route);

        $this->entity->expects($this->exactly(3))
                ->method('getDocs')
                ->will($this->onConsecutiveCalls(
                    array('api_role'=>'admin'),
                    array('api_role'=>'public'),
                    array('api_role'=>null)
                ));

        $this->assertFalse($this->entity->isPublic());
        $this->assertTrue($this->entity->isPublic());
        $this->assertTrue($this->entity->isPublic());
     }

}
