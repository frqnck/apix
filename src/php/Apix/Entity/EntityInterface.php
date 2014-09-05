<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix\Entity;

use Apix\Router;

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
     * Calls the underline/internal entity.
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
    public function parseDocs();

    /**
      * Gets the method
     *
     * @param string $name
     * @return
     */
    public function getMethod(Router $route);

    /**
     * Sets the actions array.
     *
     * @param  array $array=null An associative array of methods as keys and actions as values.
     * @return void
     */
    public function setActions(array $asso = null);

}
