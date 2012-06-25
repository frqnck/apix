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
        /* Refactoring
        $entity = new Resource();
        if( $this->isClosure($resource) ) {
            $entity->actions[$resource['method']] = $resource;
        } else {
            // assume class based
            $entity->controller = $resource;
        }
        $this->resources[$name] = $entity;
         */
        if( $this->isClosure($resource) ) {
            $this->resources[$name]['actions'][$resource['method']] = $resource;

        } else { // assume class based
            $this->resources[$name] = $resource;
        }
    }

    public function isClosure($entity)
    {
        return isset($entity['action'])
            && $entity['action'] instanceOf \Closure;
    }

    /**
     * Gets a sanitized ressource from a route object.
     *
     * @param  string   $name A resource name
     * @return string
     * @throws \InvalidArgumentException 404
     */
    public function get(Router $route)
    {
        $name = $route->name;

        if (!isset($this->resources[$name])) {
            //$name = $this->rawControllerName;
            throw new \InvalidArgumentException(
                sprintf("Invalid resource's name specified (%s).", $name), 404
            );
        }
        $entity = $this->resources[$name];

        // retrieve from controller alias
        if(isset($entity['alias'])) {
            $entity = $this->resources[$entity['alias']];
        }

        if( !$this->isClosure($entity) ) {

            // TODO: review $route->controller_*!
            $entity['controller']['name'] = isset($entity['controller']['name'])
                    ? $entity['controller']['name']
                    : $route->controller_name;

            $entity['controller']['args'] = isset($entity['controller']['args'])
                    ? $entity['controller']['args']
                    : $route->controller_args;
        }

        #d($resource);exit;
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