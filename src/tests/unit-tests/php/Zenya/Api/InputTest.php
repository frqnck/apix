<?php

namespace Zenya\Api;

class InputTest extends \PHPUnit_Framework_TestCase
{

    protected $request;

    protected function setUp()
    {
        $this->request = Request::getInstance();
    }

    protected function tearDown()
    {
        unset($this->request);
    }

    public function testGetFormatFromHttpAccept()
    {
        $request = $this->getMockBuilder('Zenya\Api\Request')->disableOriginalConstructor()->getMock();

        $request->expects($this->any())->method('hasHeader')->will($this->returnValue(true));
        $request->expects($this->any())->method('getHeader')->will(
            $this->onConsecutiveCalls('application/json', 'application/javascript', 'application/xml', 'text/xml', 'text/html')
        );

        $this->assertEquals('json', Input::getAcceptFormat($request));
        $this->assertEquals('jsonp', Input::getAcceptFormat($request));
        $this->assertEquals('xml', Input::getAcceptFormat($request));
        $this->assertEquals('xml', Input::getAcceptFormat($request));
        $this->assertEquals(false, Input::getAcceptFormat($request));
    }

    /**
     * @dataProvider inputProvider
     */
    public function testGetBodyData($type, $body, $assoc=true, $expected)
    {
        $this->request->setHeader('CONTENT_TYPE', $type);
        $this->request->setBody($body);

        $this->assertEquals($expected, Input::getBodyData($this->request, $assoc));
    }

    public function inputProvider()
    {
        $values = array('param1'=>'value1', 'param2'=>'value2');

        $xml = new Output\Xml;

        return array(
            'FormPost as array' => array(
                'format' => 'application/x-www-form-urlencoded',
                'body' => 'param1=value1&param2=value2',
                'assoc' => true,
                'expected' => $values,
                // hack to imitate web browser
                'p1' => $_REQUEST['param1'] = 'value1',
                'p2' => $_REQUEST['param2'] = 'value2'
            ),
            'JSON as array' => array(
                'type' => 'application/json',
                'body' => json_encode($values),
                'assoc' => true,
                'expected' => $values
            ),
            'JSON as object' => array(
                'type' => 'application/json',
                'body' => json_encode($values),
                'assoc' => false,
                'expected' => (object) $values
            ),
            'XML as array' => array(
                'type' => 'text/xml',
                'body' => $xml->encode($values),
                'assoc' => true,
                'expected' => $values
            ),
            'XML as object' => array(
                'type' => 'text/xml',
                'body' => $xml->encode($values),
                'assoc' => false,
                'expected' => (object) $values
            ),
            'Invalid content-type, returns null' => array(
                'type' => 'text/nullll',
                'body' => 'param1=value1&param2=value2',
                'assoc' => true,
                'expected' => null
            ),
            'no body, returns null' => array(
                'type' => 'text/nullll',
                'body' => null,
                'assoc' => true,
                'expected' => null
            ),
            'no content-type, returns null' => array(
                'type' => null,
                'body' => 'param1=value1&param2=value2',
                'assoc' => true,
                'expected' => null
            )
        );
    }

}