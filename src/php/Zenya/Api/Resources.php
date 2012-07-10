<?php

namespace Zenya\Api;

use Zenya\Api\Entity,
    Zenya\Api\Entity\EntityInterface;

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
     * @param  string $name     A resource name
     * @param  array  $resource A resource definition array
     * @return Entity
     */
    public function add($name, array $resources)
    {
        switch(true):

            case isset($resources['action'])
                && $resources['action'] instanceOf \Closure:
                $this->setEntity(
                    new Entity\EntityClosure
                );
            break;

            case isset($resources['controller']):
            default:
                $this->setEntity(
                    new Entity\EntityClass
                );

        endswitch;

        if (!isset($this->resources[$name])) {
            $entity = get_class($this->getEntity());
            $this->resources[$name] = new $entity; //new Entity($group);
        }
        $this->resources[$name]->append($resources);

        return $this->resources[$name];
    }

    /**
     * Checks wether a specified resource name exists.
     *
     * @param  string  $name The resource name to check
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
     * Gets the specified ressource entity from a route object.
     *
     * @param   Router                   $route  The resource route object.
     * @throws  /DomainException                 404
     * @return  Entity/EntityInterface
     */
    public function get(Router &$route)
    {
        $name = $route->getName();
        if (!isset($this->resources[$name])) {
            throw new \DomainException(
                sprintf('Invalid resource entity specified (%s).', $name), 404
            );
        }
        $entity = $this->resources[$name];

        // swap if aliased/redirected
        if ($redirect = $entity->getRedirect()) {
            $entity = $this->resources[$redirect];
        }
        $entity->setRoute($route);

        // handle the default actions (and allow local overrides).
        $method = $route->getMethod();
        if( !$entity->hasMethod($method)
           && $entity->hasMethod($method, $entity->getDefaultActions()) ) {
            $route->setName(
                $entity->getDefaultAction($method)
            );
            $route->setParams(array('entity' => $entity));
        
            return $this->get($route);
        }

        return $entity;
    }

}
