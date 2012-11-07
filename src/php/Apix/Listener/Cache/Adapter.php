<?php
namespace Apix\Listener\Cache;

interface Adapter
{

    /**
     * Retrieves the cache for the given id, or return false if not set.
     *
     * @param  string  $id      The cache id.
     * @return string|false     Returns the cached data.
     */
    public function load($id);

    /**
     * Saves data to the cache.
     *
     * @param  string   $data   The data to cache.
     * @param  string   $id     The cache id.
     * @param  array    $tags   The cache tags for this entry.
     * @param  int      $ttl    The time to live in seconds, if set to null the
     *                          cache is valid forever.
     * @return boolean  True on sucess.
     */
    public function save($data, $id, array $tags=null, $ttl=false);

    /**
     * Removes all the cache items associated with the given tag name.
     *
     * @param  array  $tags The tags to remove.
     * @return boolean True on sucess.
     */
    public function clean(array $tags=null);

    /**
     * Remove a specified cache record.
     *
     * @param  string $id The cache id to remove.
     * @return boolean True on sucess.
     */
    #public function remove($id);

}