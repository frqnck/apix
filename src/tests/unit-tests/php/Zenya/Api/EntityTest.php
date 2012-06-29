<?php
namespace Zenya\Api;

class EntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $entity;

    protected function setUp()
    {
        $this->entity = new Entity;
    }

    protected function tearDown()
    {
        unset($this->entity);
    }

    public function     public function testGetClassSource()
    {
        $src = $this->reflected->getSource();
        $this->assertTrue( preg_match('/^class Docbook/', $src) === 1, "Source should start by 'class ...'");
        $this->assertTrue( preg_match('/\s+}\n\n}$/', $src) === 1, "Source should end by '...}'");
    }

    public function testSpecialCharacteres()
    {
        $this->markTestIncomplete('TODO: maybe fix signle *s');
        $this->assertSame('OPTIONS /*/etc...', $this->method['api_link']);
    }

}
