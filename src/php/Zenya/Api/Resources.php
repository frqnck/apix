<?php

namespace Zenya\Api;

use Zenya\Api\Entity;
use Zenya\Api\Entity\EntityInterface;

/**
 * Represents a collection of resources.
 */
class Resources
{

    /**
     * @var array
     */
    protected $resources = array();

    /**
     * @var EntityInterface
     */
    protected $entity = null;

    /**
     * Sets an entity object.
     *
     * @param EntityInterface $entity An entity object 
     */
    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Gets the current entity object.
     *
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Adds a resource entity.
     *
     * @param  string $name         A resource name
     * @param  array  $resource     A resource definition array
     * @return void
     */
    public function add($name, array $resource)
    {
        // factory
        if( isset($resource['action'])
          && $resource['action'] instanceOf \Closure
        ) {
            $this->setEntity(new Entity\EntityClosure);
        } else { //if(isset($resource['controller'])) {
            $this->setEntity(new Entity\EntityClass);
        }
    
        if(!isset($this->resources[$name])) {
            $entity = get_class($this->getEntity());
            $this->resources[$name] = new $entity; //new Entity($group);
        }
        $this->resources[$name]->append($resource);
        return $this->resources[$name];
    }

    /**
     * Checks a specified resource name exists.
     *
     * @param  string $name     The resource name to check
     * @return boolean
     */
    public function has($name)
    {
        return isset($this->resources[$name]);
    }

    /**
     * Returns all the resources.
     *
     * @return array The array of resources
     */
    public function toArray()
    {
        return $this->resources;
    }

    /**
     * Gets the specified ressource entity.
     *
     * @param  string   $name A resource name
     * @return Zenya/Api/Entity/EntityInterface
     */
    public function get($name)
    {
        $entity = $this->resources[$name];

        // TODO: swap if aliased
        if($redirect = $entity->getRedirect()) {
            $entity = $this->resources[$redirect];
        }

        /*
        if( !$entity->isClosure() ) {
            if(!isset($entity->controller['name'])) {
                $entity->controller['name'] = $route->controller_name;
            }

            if(!isset($entity->controller['args'])) {
                $entity->controller['args'] = $route->controller_args;
            }
        }
        */

        return $entity;
    }

}