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
    Apix\Entity\EntityInterface,
    Apix\Entity\EntityClosure;

class EntityClosureTest extends \PHPUnit_Framework_TestCase
{

    public $definition = null;

    protected $entity, $route;

    protected function setUp()
    {
        $this->definition = array('action'=>function($id, $optional=null){return func_get_args();}, 'method'=>'GET', 'redirect' => 'location' );

        $this->entity = new Entity\EntityClosure;
        $this->entity->append($this->definition);

        $routes = array('/:controller/:id/:optional' => array());
        $this->route = new Router($routes);
        $this->route->setMethod('GET');
    }

    protected function tearDown()
    {
        unset($this->entity, $this->route);
    }

    public function testAppend()
    {
        $entity = $this->entity->toArray();

        $this->assertTrue($entity['actions']['GET']['action'] instanceOf \Closure);
        $this->assertTrue(is_callable($entity['actions']['GET']['action']));

        $this->assertSame('location', $entity['redirect'], "Check to see if parent::_append is called.");
    }

    public function testUnderlineCall()
    {
        $this->route->map('/controller/1234');

        $results = $this->entity->underlineCall($this->route);
        $this->assertSame(array('1234'), $results);
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       405
     */
    public function testCallThrowsInvalidArgumentException()
    {
        $this->route->map('/controller/id');
        $this->route->setMethod('XXX');
        $this->entity->underlineCall($this->route);
    }

    /**
     * @expectedException           \BadMethodCallException
     * @expectedExceptionCode       400
     */
    public function testCallThrowsBadMethodCallException()
    {
        $this->route->map('/controller');
        $this->entity->underlineCall($this->route);
    }

    public function testParseDocsGroupLevel()
    {
        $this->entity->group("/* TODO {closure-group-title} */");
        $docs = $this->entity->parseDocs();
        $this->assertSame("TODO {closure-group-title} ", $docs['title']);
        $this->assertSame(1, count($docs['methods']));
    }

    public function testGetMethod()
    {
        $method = $this->entity->getMethod($this->route);
        $this->assertInstanceOf('ReflectionFunction',  $method, "Shoulf be a ReflectionFunction instance");
        $this->assertSame('{closure}', $method->getShortName());
    }

    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       405
     */
    public function testGetMethodThrowsInvalidArgumentException()
    {
        $this->route->map('/controller/id');
        $this->route->setMethod('PUT');

        $this->entity->getMethod($this->route);
    }

    public function testGetActions()
    {
        $actions = $this->entity->getActions();
        $this->assertSame(1, count($actions));
        $this->assertSame('GET', key($actions));
    }

    public function testAddRedirect()
    {
        $actions = $this->entity->redirect('paris');
        $entity = $this->entity->toArray();

        $this->assertSame('paris', $entity['redirect']);
    }

    public function testReflectedFunc()
    {
        $func = $this->entity->reflectedFunc('GET');
        $this->assertInstanceOf('ReflectionFunction', $func, "Shoulf be a ReflectionFunction instance");

        $this->assertSame($func, $this->entity->reflectedFunc('GET'));
    }

    public function testReflectedFuncReturnsFalse()
    {
        $this->assertFalse(
            $this->entity->reflectedFunc('non-existant')
        );
    }

}
