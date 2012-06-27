<?php

namespace Zenya\Api;

use Zenya\Api\Entity;

/**
 * Represents a collection of resources.
 */
class Resources
{

    protected $resources = array();

    /**
     * Adds a resource entity.
     *
     * @param  string $name     The resource name
     * @param  array  $resource The resource definition array
     * @return void
     */
    public function add($name, array $resource, $group=null)
    {
        if(!isset($this->resources[$name])) {
            $this->resources[$name] = new Entity($group);
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
     * Gets the specified ressource entity.
     *
     * @param  string   $name A resource name
     * @return Zenya\Api\Entity\Entity
     */
    public function get($name)
    {
        $entity = $this->resources[$name];

        // swap if aliased
        if(isset($entity->alias)) {
            $name = $entity->alias;
            $entity = $this->resources[$name];
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

    /**
     * Returns all the resources.
     *
     * @return array The array of resources
     */
    public function toArray()
    {
        return $this->resources;
    }

}