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

    public function testSpecialCharacteres()
    {
        $this->markTestIncomplete('TODO');
    }

}
