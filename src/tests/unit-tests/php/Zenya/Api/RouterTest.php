<?php
namespace Zenya\Api;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionCode       500
     */
    public function testConstructorThrowsExceptionWhenNotAssociative()
    {
        new Router( array('/:controller/:action/:grab') );
    }

    public function testMapMerging()
    {
        $r1 = array('/test'=>'/:test');
        $r2 = array('/test2'=>'/:test2');
        $this->assertSame($r1+$r2, array_merge($r1, $r2));

        $route = new Router( array() );
        $route->setParams($r1);       
        $route->map('/', $r2);
        $this->assertSame($r1+$r2, $route->getParams());
    }

    /**
     * @covers Zenya\Api\Router::__construct
     */
    public function testEmptyConstructor()
    {
        $rules = array();
        $route = new Router( $rules );
        $route->map('/');
        $this->assertSame(null, $route->getController());
        $this->assertSame(null, $route->getAction());
        $this->assertEquals(array(), $route->getParams());
    }

    /**
     * @covers Zenya\Api\Router::__construct
     */
    public function testBasicConstructor()
    {
        $rules = array('/:one/:two/:three' => array('controller'=>'implyController', 'action'=>'implyAction'));
        $route = new Router( $rules );
        $route->map('/controller/action/123');
        $this->assertSame('implyController', $route->controller);
        $this->assertSame('implyAction', $route->getAction());
        $this->assertEquals(123, $route->params['three']);
    }

    /**
     * @dataProvider urlsProvider
     */
    public function testManyRoutes($url, $expected)
    {
        $routes = array(
            'defaults'=>array('action'=>'defaultAction', 'controller'=>'defaultController'),
            'rules'=>array(
                '/v1/keywords/:category/page' => array('controller'=>'keywords', 'action'=>'list'),
                'http://api.dev/v1/keywords/:category/:page' => array('controller'=>'keywords', 'action'=>'get'),
                'http://api.dev/v1/:controller/:action/:id' => array(),
                'http://api.dev/news/:country/:city/:optional' => array('controller'=>'News'),
                '/v2/:one/:two/:three' => array('controller'=>'numbers', 'action'=>'translate'),
                '/book/:book/author/:author/pages/:pages' => array('controller'=>'Library', 'action'=>'borrow'),
                '/:controller/:action/:id' => array('controller'=>'lastOne')
            )
        );
        $route = new Router($routes['rules'], $routes['defaults']);
        $route->map($url);

        $this->assertSame($expected['controller'], $route->controller);
        $this->assertSame($expected['action'], $route->getAction());
        $this->assertEquals($expected['params'], $route->params);

        if ( isset($route->newProp) ) {
            $this->assertSame($results['newProp'], $route->newProp);
        }
    }

    public function urlsProvider()
    {
        return array(
            'not-matching, defaulting'=>array(
                'url'=>'not-matching/one1/two2/three3',
                'expected'=>array('controller'=>'defaultController', 'action'=>'defaultAction', 'params'=>array())
            ),
            array(
                'url'=>'http://api.dev/v1/one/two/three',
                'expected'=>array('controller'=>'one', 'action'=>'two', 'params'=>array('id'=>'three','controller'=>'one','action'=>'two'))
            ),
            array(
                'url'=>'/v2/un/deux/trois/quatre',
                'expected'=>array('controller'=>'numbers','action'=>'translate','params'=>array('three'=>'trois','two'=>'deux','one'=>'un'))
            ),
            array(
                'url'=>'http://api.dev/news/fr/paris',
                'expected'=>array('controller'=>'News', 'action'=>'defaultAction', 'params'=>array('country'=>'fr','city'=>'paris'))
            ),
            array(
                'url'=>'http://api.dev/news/fr/paris/option',
                'expected'=>array('controller'=>'News', 'action'=>'defaultAction', 'params'=>array('country'=>'fr','city'=>'paris', 'optional'=>'option'))
            ),
            array(
                'url'=>'/book/Un+livre/author/Un+auteur/pages/500',
                'expected'=>array('controller'=>'Library', 'action'=>'borrow', 'params'=>array('pages'=>500,'author'=>'Un+auteur', 'book'=>'Un+livre'))
            )
        );
    }

    /**
     * @dataProvider routesProvider
     */
    public function testRoutes($r)
    {
        extract($r);
        $route = new Router($rules, $defaults);
        $route->map($url);
        $this->assertSame($results['controller'], $route->controller);
        $this->assertSame($results['action'], $route->getAction());
        $this->assertEquals($results['params'], $route->params);

        if ( isset($route->newProp) ) {
            $this->assertSame($results['newProp'], $route->newProp);
        }
    }

    public function routesProvider()
    {
        // $rules, $url, $defaults, $expected
        return array(
            'test all empty'=>array(array(
                'rules'=>array('' => array()),
                'url'=>'',
                'defaults'=>array(),
                'results'=>array('controller'=>null, 'action'=>null, 'params'=>array())
             )),
            'test with a new prop'=>array(array(
                'rules'=>array('' => array()),
                'url'=>'',
                'defaults'=>array('newProp'=>12345),
                'results'=>array('controller'=>null, 'action'=>null, 'newProp'=>12345, 'params'=>array())
             )),
            'test basic parameters allocation'=>array(array(
                'rules'=>array('/:controller/:beer/:quantity' => array('action'=>'drink')),
                'url'=>'/pub/stella/6',
                'defaults'=>array('controller'=>'home'),
                'results'=>array('controller'=>'pub', 'action'=>'drink', 'params'=>array('controller'=>'pub','beer'=>'stella', 'quantity'=>6))
             )),
            'test parameters allocation and that the rules have precedence'=>array(array(
                'rules'=>array('/:controller/:id/:item' => array('action'=>'sleep', 'controller'=>'home')),
                'url'=>'/beers/stella/price',
                'defaults'=>array('controller'=>'home'),
                'results'=>array('controller'=>'home', 'action'=>'sleep', 'params'=>array('controller'=>'beers','id'=>'stella', 'item'=>'price'))
             )),
            'test matched prevails when all the params are allocated'=>array(array(
                'rules'=>array('/:id/:item'=>array('controller'=>'matchedController','action'=>'matchedAction')),
                'url'=>'/stella/price',
                'defaults'=>array('controller'=>'home'),
                'results'=>array('controller'=>'matchedController', 'action'=>'matchedAction', 'params'=>array('id'=>'stella', 'item'=>'price'))
             )),
            'test with all the entries allocated'=>array(array(
                'rules'=>array('/:controller/:action/:id'=>array('controller'=>'toilet')),
                'url'=>'/pub/piss/stella',
                'defaults'=>array('controller'=>'home'),
                'results'=>array('controller'=>'toilet', 'action'=>'piss', 'params'=>array('id'=>'stella', 'action'=>'piss', 'controller'=>'pub'))
             )),
            'test with optional rule entry' => array(array(
                'rules'=>array('/:controller/:action/:id/:optional'=>array('controller'=>'toilet')),
                'url'=>'/pub/piss/stella',
                'defaults'=>array('controller'=>'home'),
                'results'=>array('controller'=>'toilet', 'action'=>'piss', 'params'=>array('id'=>'stella', 'action'=>'piss', 'controller'=>'pub'))
             )),
             'test giberish'=>array(array(
                'rules'=>array('/dasdasd/:test/dasdasda' => array()),
                'url'=>'/dasdasd/sasa/dasdasda',
                'defaults'=>array(),
                'results'=>array('controller'=>null, 'action'=>null, 'params'=>array('test'=>'sasa'))
             )),
            'test giberish'=>array(array(
                'rules'=>array('/dasdasd/:q/:z//:y' => array()),
                'url'=>'/dasdasd/sasa/baba//dasda\/sda',
                'defaults'=>array(),
                'results'=>array('controller'=>null, 'action'=>null, 'params'=>array('q'=>'sasa', 'z'=>'baba', 'y'=>'dasda\\'))
             )),

        );
    }

   /**
     * @dataProvider propertiesProvider
     */
    public function testSetMainProperties($r)
    {
        extract($r);
        $route = new Router(array(), $defaults);
        $route->setMainProperties($rules, $params);

        $this->assertSame($results['controller'], $route->controller);
        $this->assertSame($results['action'], $route->getAction());
        $this->assertEquals($results['params'], $route->params);

        if (isset($route->newProperty)) {
            $this->assertSame($results['newProperty'], $route->newProperty);
        }
    }

    public function propertiesProvider()
    {
        return array(
            'everything set'=> array(array(
                'rules'=>array('action'=>'ruled', 'controller'=>'ruled'),
                'params'=>array('param1'=>'val1','action'=>'fromQueryString','controller'=>'fromQueryString'),
                'defaults'=>array('action'=>'defaultAction','controller'=>'defaultController'),
                'results'=>array('controller'=>'ruled', 'action'=>'ruled', 'params'=>array('param1'=>'val1','action'=>'fromQueryString','controller'=>'fromQueryString'))
            )),
            'set by params and add a new prop'=> array(array(
                'rules'=>array('action'=>'drink'),
                'params'=>array('controller'=>'pub'),
                // An added prop, might be useful at some stage!
                'defaults'=>array('newProperty'=>'someValue'),
                'results'=>array('newProperty'=>'someValue', 'controller'=>'pub', 'action'=>'drink', 'params'=>array('controller'=>'pub'))
            )),
            'defaults prevail'=> array(array(
                'rules'=>array('action'=>null),
                'params'=>array(),
                'defaults'=>array('controller'=>'defaultController'),
                'results'=>array('controller'=>'defaultController', 'action'=>null, 'params'=>array())
            )),
            'rule prevail'=> array(array(
                'rules'=>array('action'=>'ruled'),
                'params'=>array('action'=>'fromQueryString'),
                'defaults'=>array('action'=>'defaultAction'),
                'results'=>array('controller'=>null, 'action'=>'ruled', 'params'=>array('action'=>'fromQueryString'))
            )),
            'params prevail'=> array(array(
                'rules'=>array(),
                'params'=>array('action'=>'fromQueryString'),
                'defaults'=>array('action'=>'defaultAction'),
                'results'=>array('controller'=>null, 'action'=>'fromQueryString', 'params'=>array('action'=>'fromQueryString'))
            )),
            'defaults prevail'=> array(array(
                'rules'=>array(),
                'params'=>array(),
                'defaults'=>array('action'=>'defaultAction'),
                'results'=>array('controller'=>null, 'action'=>'defaultAction', 'params'=>array())
            )),

        );
    }

    /**
     * @dataProvider ruleProvider
     */
    public function testRuleMatch($rule, $url, $expected)
    {
        $router = new Router(array(), array('controller'=>'home'));
        $results = $router->ruleMatch($rule, $url);
        $this->assertSame($expected, $results);
    }

    public function ruleProvider()
    {
        return array(
             array('/:a/:b/:c', '/a/b/c', array('a'=>'a','b'=>'b','c'=>'c')),
             array('/:a', '/a/b/c', array('a'=>'a')),
             array('/:1/:2', '/a', array('1'=>'a')),
             array('/:first', '', array()),
             array('/:slash', '/', array('slash'=>'')),
             array('/:A/:B', '//b/c', array('A'=>'', 'B'=>'b')),
             array('', '/', array()),
             array('', '', array()),
             array('prefix/:c', 'prefix/c', array('c'=>'c')),
             array('/prefix/:c', '/prefix/c', array('c'=>'c')),
             array('/prefix/:c', '/prefix/c/etc...', array('c'=>'c')),
             // false
             array('/prefix/:c', 'badprefix/c', false),
             array('prefix/:c', '/prefix/c', false),
             array('prefix/:c', '/prefix/c', false),
        );
    }

    public function testGetActions()
    {
        $router = new Router(array());
        $actions = array(
            'POST'      => 'onCreate',
            'GET'       => 'onRead',
            'PUT'       => 'onUpdate',
            'DELETE'    => 'onDelete',
            'OPTIONS'   => 'onHelp',
            'HEAD'      => 'onTest',
            'TRACE'     => 'onTrace'
        );
        $this->assertSame($actions, $router->getActions());
    }

    public function testGetSetActions()
    {
        $router = new Router(array());
        $router->setAction('PUT');
        $this->assertSame('onUpdate', $router->getAction() );
    }

    public function testGetSetController()
    {
        $router = new Router(array());
        $router->setController('aContollerStr');
        $this->assertSame('aContollerStr', $router->getController() );
    }

    public function testGetSetMethod()
    {
        $router = new Router(array());
        $router->setMethod('aMethodStr');
        $this->assertSame('aMethodStr', $router->getMethod() );
    }
}
