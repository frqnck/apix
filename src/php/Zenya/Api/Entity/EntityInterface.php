<?php

namespace Zenya\Api\Entity;

use Zenya\Api\Router;

interface EntityInterface
{

    /**
 	 * Append the given array definitions.
	 *
	 * @param array $definitions
	 * @return void
	 */
	public function _append(array $defs);

    /**
 	 * Calls the underline entity.
	 *
	 * @param Router $route
	 * @return array
	 * @throws InvalidArgumentException 405
	 */
    public function _call(Router $route);

    /**
 	 * Parses the PHP docs.
	 *
	 * @return void
	 */
    function _parseDocs();

    /**
 	 * Gets the method
	 *
	 * @param string $name
	 * @return
	 */
    function getMethod(Router $route);

    /**
     * Returns an array of method keys and action values.
     *
     * @param  array $array
     * @return array
     */
    function getActions();

}