<?php
namespace Apix\Plugins\Cache;

interface Adapter
{

    /**
     * Retrieves the cache for the given key, or return false if not set.
     *
     * @param  string  $key     The cache id to retrieve.
     * @return string|false     Returns the cached data.
     */
    public function load($key);

    /**
     * Saves data to the cache.
     *
     * @param  string   $data   The data to cache.
     * @param  string   $key    The cache id to save.
     * @param  array    $tags   The cache tags for this entry.
     * @param  int      $ttl    The time to live in seconds, if set to null the
     *                          cache is valid forever.
     * @return boolean          Returns True on success.
     */
    public function save($data, $key, array $tags=null, $ttl=false);

    /**
     * Removes all the cached entries associated with the given tag names.
     *
     * @param  array  $tags     The array of tags to remove.
     */
    public function clean(array $tags);

    /**
     * Deletes the specified cache record.
     *
     * @param  string $key      The cache id to remove.
     * @return boolean          Returns True on success.
     */
    public function delete($key);

    /**
     * Flush all the cached entries.
     *
     * @param   boolean $all    Wether to flush the whole database.
     * @return  boolean         Returns True on success.
     */
     public function flush($all=false);

}