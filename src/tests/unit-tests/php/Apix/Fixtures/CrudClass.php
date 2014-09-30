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

namespace Apix\Fixtures;

/**
 * CRUD fixture class
 *
 * @api_public          true
 * @api_version         1.0
 */
class CrudClass
{

    /**
     * Create a new entity
     *
     * @param  string $data
     * @param  array  $optional
     * @return array
     */
    public function onCreate($data, array $optional=null)
    {
        return func_get_args();
    }

    /**
     * Read an entity, or list many
     *
     * @param  integer $id       an integer
     * @param  array   $optional
     * @return array
     */
    public function onRead($id, array $optional=null)
    {
        #echo $id, $optional;

        return func_get_args();
    }

    /**
     * Update an entity
     *
     * @param  string $name
     * @param  array  $optional
     * @return array
     */
    public function onUpdate($id, array $optional=null)
    {
        return func_get_args();
    }

    /**
     * Delete an entity
     *
     * @param  string $name
     * @param  array  $optional
     * @return array
     */
    public function onDelete($id, array $optional=null)
    {
        return func_get_args();
    }

}
