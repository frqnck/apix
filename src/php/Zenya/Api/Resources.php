<?php

namespace Zenya\Api;

/**
 * Represents a collection of resources.
 *
 */
class Resources
{

    private $resources = array();

    /**
     * Adds a resource entity
     *
     * @param  string $name     The resource name
     * @param  array  $resource The resource definition array
     * @return void
     */
    public function add($name, array $resource)
    {
        if(!isset($this->resources[$name])) {
            $this->resources[$name] = new Entity();
        }
        $this->resources[$name]->append($resource);
    }

    /**
     * Gets a ressource entity from a Router object.
     *
     * @param  string   $name A resource name
     * @return string
     * @throws \InvalidArgumentException 404
     */
    public function get(Router $route)
    {
        $name = isset($route->name) ? $route->name : $route->path;
        try {
            if (isset($this->resources[$name])) {
                $entity = $this->resources[$name];

                // swap if aliased
                if(isset($entity->alias)) {
                    $name = $entity->alias;
                    $entity = $this->resources[$name];
                }

                /*
                if( !$entity->isClosure() ) {

                    // TODO: review $route->controller_*!
                    $entity->controller->name = isset($entity->controller->name)
                            ? $entity->controller->name
                            : $route->controller_name;

                    $entity->controller->args = isset($entity->controller->args)
                            ? $entity->controller->args
                            : $route->controller_args;
                }
                */

                return $entity;
            }
        } catch(\Exception $e) {
            // $name = $this->rawControllerName;
            throw new \InvalidArgumentException(
                sprintf("Invalid resource's name specified (%s).", $name), 404
            );
        }
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