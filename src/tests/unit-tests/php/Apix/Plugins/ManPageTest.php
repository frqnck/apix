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
        $this->response->results['help'] = array();
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

        $this->options = array(
            'enable'   => true,
            // 'view_dir' => '/tmp',
        );

        $this->plugin = new ManPage($this->options);
    }

    protected function tearDown()
    {
        unset($this->plugin, $this->response, $this->route, $this->options);
    }

    public function testIsDisable()
    {
        $plugin = new ManPage( array('enable' => false) );
        $this->assertFalse( $plugin->update($this->response) );
    }

    /**
     * @dataProvider urlsProvider
     */
    public function testGetUrlApiAndVersion($uri, $exp)
    {
        // $this->assertSame($exp, ManPage::getUrlApiAndVersion($uri, '/h'));
        $_SERVER['SCRIPT_URI'] = $uri;

        $plugin = new ManPage($this->options);
        $opts = $plugin->getOptions();
        $this->assertSame($exp[0], $opts['url_api'], "Extracted 'url_api' failed.");
        $this->assertSame($exp[1], $opts['version'], "Extracted 'version' failed.");
    }

    public function urlsProvider()
    {
        return array(
            array(
                'uri' => '/',
                'exp' => array(null, 'v1')
            ),
            array(
                'uri' => '/help/foo',
                'exp' => array(null, 'v1')
            ),
            array(
                'uri' => 'foo/v1/help/bar',
                'exp' => array('foo/v1', 'v1')
            ),
            array(
                'uri' => '/v333/help/bar',
                'exp' => array('/v333', 'v333')
            )
        );
    }

    /**
     * @group test
     */
    public function testUpdate()
    {
        $this->plugin->update($this->response);
        // $this->assertSame($exp, ManPage::getUrlApiAndVersion($uri, '/h'));
    }

}
