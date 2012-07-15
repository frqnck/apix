<?php

namespace Zenya\Api;

class HttpRequestTest extends \PHPUnit_Framework_TestCase
{

    protected $request;

    protected function setUp()
    {
        $this->request = HttpRequest::getInstance();
    }

    protected function tearDown()
    {
        unset($this->request);
    }

    public function testIsExtendingFromRequest()
    {
        $this->assertInstanceOf(__NAMESPACE__ . '\Request', $this->request);
    }

    /**
     * TEMP
     */
    public function testIsSingleton()
    {
        $req = HttpRequest::getInstance();
        $req2 = HttpRequest::getInstance();

        $this->assertSame($req, $req2);
    }

    /**
     * TEMP
     * @covers Zenya\Api\HttpRequest::__clone
     */
    public function testIsSingletonIsNotClonable()
    {
        // $r = clone $this->request;
        $r = new \ReflectionClass($this->request);
        $p = $r->getMethods(\ReflectionMethod::IS_PRIVATE|\ReflectionMethod::IS_FINAL);
        $this->assertSame('__clone', $p[0]->name);
    }

    public function testGetFormatFromHttpAccept()
    {
        $req = $this->getMock('Zenya\Api\HttpRequest', array('hasHeader', 'getHeader'));

        $req->expects($this->any())->method('hasHeader')->will($this->returnValue(true));
        $req->expects($this->any())->method('getHeader')->will(
            $this->onConsecutiveCalls('application/json', 'application/javascript', 'application/xml', 'text/xml', 'text/html')
        );

        $this->assertEquals('json', $req->getAcceptFormat());
        $this->assertEquals('jsonp', $req->getAcceptFormat());
        $this->assertEquals('xml', $req->getAcceptFormat());
        $this->assertEquals('xml', $req->getAcceptFormat());
        $this->assertEquals(false, $req->getAcceptFormat());
    }

    /**
     * @dataProvider bodyDataProvider
     */
    public function testGetBodyData($type, $body, $assoc=true, $expected, array $formats=null)
    {
        if($formats !== null) {
           $this->request->setFormats($formats);
        }
        $this->request->setHeader('CONTENT_TYPE', $type);
        $this->request->setBody($body);

        $this->assertEquals(
            $expected,
            $this->request->getBodyData($assoc)
        );
    }

    public function bodyDataProvider()
    {
        $values = array('param1'=>'value1', 'param2'=>'value2');

        $xml = new Output\Xml;

        return array(
            'x-www-form as array' => array(
                'format' => 'application/x-www-form-urlencoded',
                'body' => 'param1=value1&param2=value2',
                'assoc' => true,
                'expected' => $values,
                'formats' => array('post', 'xml', 'json'),
                // hack to imitate a web browser
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
            ),
            'Not set in formats should return null' => array(
                'type' => 'application/json',
                'body' => json_encode($values),
                'assoc' => true,
                'expected' => null, // maybe should actually throw an exception?
                'formats' => array('XML')
            ),
            // TODO: CSV eventually?
            // 'CSV as array' => array(
            //     'type' => 'text/csv',
            //     'body' => "param1,param2\nvalue1,value2",
            //     'assoc' => true,
            //     'expected' => $values,
            //     'formats' => array('XML')
            // )

        );
    }

    public function testSetFormats()
    {
        $formats = array('json', 'xml', 'csv');
        $this->request->setFormats($formats);

        $r = new \ReflectionObject($this->request);
        $req = $r->getProperty('formats');
        $req->setAccessible(true);

        $this->assertSame(
            $formats,
            $req->getValue($this->request)
        );
    }

}