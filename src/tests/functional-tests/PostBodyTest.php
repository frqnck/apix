<?php

class PostBodyTest extends \PHPUnit_Framework_TestCase
{

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
        $array = array('param1'=>'value1', 'param2'=>'value2');
        $r = $this->post(json_encode($array), 'json');

        $this->assertEquals('{"param1":"value1","param2":"value2"}', $r->zenya->upload->body);
    }

    public function testJsonAsArray()
    {
        $array = array('param1'=>'value1', 'param2'=>'value2');
        $r = $this->post(json_encode($array), 'json');

        $this->assertSame($array, (array)$r->zenya->upload->params);
    }

    public function testXmlAsString()
    {
        $array = array('param1'=>'value1', 'param2'=>'value2');

        $xml = new Zenya\Api\Output\Xml;
        $r = $this->post($xml->encode($array), 'xml');

        $this->assertEquals(
            '<?xml version="1.0" encoding="utf-8"?><root><param1>value1</param1><param2>value2</param2></root>',
            preg_replace('/[\r\n]+/', '', $r->zenya->upload->body)
        );
    }

   public function testXmlAsArray()
    {
        $array = array('param1'=>'value1', 'param2'=>'value2');

        $xml = new Zenya\Api\Output\Xml;
        $r = $this->post($xml->encode($array), 'xml');

        $this->assertSame($array, (array)$r->zenya->upload->params);
    }


}