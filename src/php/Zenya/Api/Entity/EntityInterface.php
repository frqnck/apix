<?php

namespace Zenya\Api\Entity;

use Zenya\Api\Entity,
    Zenya\Api\Router;

interface EntityInterface
{

    /**
     * Appends the given array definition and apply _local_ mappings.
     *
     * @param  array $definitions
     * @return void
     */
    public function append(array $defs);

    /**
      * Calls the underline entity.
     *
     * @param  Router                   $route
     * @return array
     * @throws InvalidArgumentException 405
     */
    public function underlineCall(Router $route);

    /**
      * Parses the PHP docs.
     *
     * @return void
     */
    public function _parseDocs();

    /**
      * Gets the method
     *
     * @param string $name
     * @return
     */
    public function getMethod(Router $route);

    /**
     * Returns an array of method keys and action values.
     *
     * @param  array $array
     * @return array
     */
    public function getActions();

}
