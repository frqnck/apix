<?php

namespace Zenya\Api;

class ExperimentTest extends \PHPUnit_Framework_TestCase
{

    protected $server, $request;

    protected function setUp()
    {
        $this->config = Config::getInstance(true);

        $this->request = $this->getMockBuilder('Zenya\Api\Request')->disableOriginalConstructor()->getMock();

        $this->response = $this->getMockBuilder('Zenya\Api\Response')->setConstructorArgs(array('request'=>$this->request))->getMock();
        $this->response->unit_test = true;

        $this->api = new Server(null, $this->request, $this->response);

        #$this->server = new Server(new Config, $this->request);
    }

    protected function tearDown()
    {
        unset($this->server);
        unset($this->request);
    }

    public function OfftestOnReadUpdateRoutesInConfig()
    {
        $this->api->onRead('/keywords/:keyword', function($keyword) {
            return array('keyword'=>$keyword);
        });

        $routes = $this->config->getRoutes();
        $this->assertArrayHasKey('/keywords/:keyword', $routes);
        $this->assertArrayHasKey('GET', $routes['/keywords/:keyword']);
        $this->assertArrayHasKey('action', $routes['/keywords/:keyword']['GET']);
    }

    public function testAccessOnReadMapsWithRoute()
    {
        $this->api->onRead('/keywords/:keyword', function($keyword) {
            return array('keyword'=>$keyword);
        });

        $this->request->expects($this->any())->method('getUri')->will($this->returnValue('/keywords/keywordToTest'));
        $this->request->expects($this->any())->method('getMethod')->will($this->returnValue('GET'));

        $this->api->setRouting($this->request, $this->api->resources->toArray(), $this->config->get('routing'));

        echo $this->api->run();

        echo 'END';
    }

    public function OfftestAccessReadWith()
    {
        $this->api->onRead('/keywords/:keyword', function($keyword) {
            return array('GET Keyword' => $keyword);
        });

        $this->api->onPost('/keywords/:keyword', function($keyword) {
            return array('POST Keyword' => $keyword);
        });

        $this->assertArrayHasKey('/keywords/:keyword', $routes);
        $this->assertArrayHasKey('GET', $routes['/keywords/:keyword']);
        $this->assertArrayHasKey('POST', $routes['/keywords/:keyword']);

        #d($this->api->getResources());exit;

        $this->request->expects($this->once())->method('getUri')->will($this->returnValue('/keywords/keywordToTest'));

        $this->api->setRouting($this->request, $this->config->getRoutes(), $this->config->get('routing'));
        echo $this->api->run();
        echo 'END';
    }

}
