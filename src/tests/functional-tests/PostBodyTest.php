<?php

class PostBodyTest extends \PHPUnit_Framework_TestCase
{

    protected $debug = null;

    public function post($body, $format=null, $url='http://zenya.dev/index.php/api/v1/upload.json/keyword')
    {
        $cmd = "curl -s -X POST -d '${body}' '${url}'";

        if($format == 'json') {
            $cmd .= ' --header "Content-Type:application/json"';
        } else if($format == 'xml') {
            $cmd .= ' --header "Content-Type:text/xml"';
        } else {
            $cmd .= ' --header "Content-Type:application/x-www-form-urlencoded"';
        }

        $json = exec($cmd, $this->debug);

        return json_decode($json);
    }


    public function testFormPostAsString()
    {
        $r = $this->post('param1=value1&param2=value2');

        $this->assertEquals('param1=value1&param2=value2', $r->zenya->upload->body);
    }

   public function testFormPostAsArray()
    {
        $r = $this->post('param1=value1&param2=value2');

        $this->assertSame(array('param1'=>'value1', 'param2'=>'value2'), (array)$r->zenya->upload->params);
    }

    public function testJsonAsString()
    {
        $values = array('param1'=>'value1', 'param2'=>'value2');
        $r = $this->post(json_encode($values), 'json');

        $this->assertEquals('{"param1":"value1","param2":"value2"}', $r->zenya->upload->body);
    }

    public function testJsonAsArray()
    {
        $values = array('param1'=>'value1', 'param2'=>'value2');
        $r = $this->post(json_encode($values), 'json');

        $this->assertSame($values, (array)$r->zenya->upload->params);
    }

    public function testXmlAsString()
    {
        $values = array('param1'=>'value1', 'param2'=>'value2');

        $xml = new Zenya\Api\Output\Xml;
        $r = $this->post($xml->encode($values), 'xml');

        $this->assertEquals(
            '<?xml version="1.0" encoding="utf-8"?><root><param1>value1</param1><param2>value2</param2></root>',
            preg_replace('/[\r\n]+|[\s]{2,}/', '', $r->zenya->upload->body)
        );
    }

   public function testXmlAsArray()
    {
        $values = array('param1'=>'value1', 'param2'=>'value2');

        $xml = new Zenya\Api\Output\Xml;
        $r = $this->post($xml->encode($values), 'xml');

        $this->assertSame($values, (array)$r->zenya->upload->params);
    }


}