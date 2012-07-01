<?php

class PostBodyTest extends \PHPUnit_Framework_TestCase
{

    public $debug = null;

    public function setUp()
    {
    }

    public function post($body, $format=null, $url='http://sleepover.dev/index.php/api/v1/upload.json/keyword')
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


    public function testBasic()
    {
        $r = $this->post('param1=value1&param2=value2');

        $this->assertEquals('param1=value1&param2=value2', $r->zenya->upload->body);
        $this->assertSame(array('param1'=>'value1', 'param2'=>'value2'), (array)$r->zenya->upload->params);
    }

    public function testJson()
    {
        $array = array('param1'=>'value1', 'param2'=>'value2');
        $r = $this->post(json_encode($array), 'json');

        $this->assertEquals('{"param1":"value1","param2":"value2"}', $r->zenya->upload->body);
        $this->assertSame($array, (array)$r->zenya->upload->paramsBody);
    }

    public function testXml()
    {
        $array = array('param1'=>'value1', 'param2'=>'value2');

        $xml = new Zenya\Api\Output\Xml;
        $r = $this->post($xml->encode($array), 'xml');

        $this->assertEquals('<?xml version="1.0" encoding="utf-8"?>'.PHP_EOL."<root>\n  <param1>value1</param1>\n  <param2>value2</param2>\n</root>",
            $r->zenya->upload->body);
        $this->assertSame($array, (array)$r->zenya->upload->paramsBody);
    }

}