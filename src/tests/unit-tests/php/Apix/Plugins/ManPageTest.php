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

namespace Apix\Plugin;

use Apix\HttpRequest,
    Apix\Response,
    Apix\Service,
    Apix\TestCase;

class ManPageTest extends TestCase
{
    protected $plugin, $response, $route;

    public function setUp()
    {
        $_SERVER['SCRIPT_URI'] = 'https://apix.ouarz.net/v33/help/test';

        $this->request = new HttpRequest();
        $this->response = new Response($this->request);
        $this->response->unit_test = true;

        Service::set('response', $this->response);

        $this->route = $this->getMock('Apix\Router');

        $this->route->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('/resource'));

        $this->route->expects($this->any())
            ->method('getController')
            ->will($this->returnValue('resource'));

        $this->response->setRoute($this->route);

        $options = array(
            'enable'   => true,
            'view_dir' => '/tmp',
        );

        $this->plugin = new ManPage($options);
    }

    protected function tearDown()
    {
        unset($this->plugin, $this->response, $this->route);
    }

    public function testIsDisable()
    {
        $plugin = new ManPage( array('enable' => false) );
        $this->assertFalse( $plugin->update($this->response) );
    }

    /**
     * @group test
     * @dataProvider urlsProvider
     */
    public function testGetUrlApiAndVersion($uri, $exp)
    {
        // $this->assertSame($exp, ManPage::getUrlApiAndVersion($uri, '/h'));
        $_SERVER['SCRIPT_URI'] = $uri;

        $plugin = new ManPage;
        $opts = $plugin->getOptions();

        $this->assertSame($exp, $opts);
    }

    public function urlsProvider()
    {
        return array(
            // array(
            //     'uri' => '',
            //     'exp' => array()
            // ),
            array(
                'uri' => 'foo/v33/h',
                'exp' => array('foo/v33/h', 'foo/v33', 'v33')
            ),
            // array(
            //     'uri' => '/x/y/h',
            //     'exp' => array('/x/y/h', '/x/y')
            // ),
        );
    }

}
