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

namespace Apix\Plugin;

use Apix\Entity;

abstract class PluginAbstractEntity extends PluginAbstract
{
    /**
     * The annotation key string for thsi plugin, e.g. "api_NAME".
     * @var string
     */
    protected $annotation;

    /**
     * Holds this plugin entity.
     * @var Entity
     */
    protected $entity;

    /**
     * Holds all the extracted subtags.
     * var array|null
     */
    private $subtags_extract = null;

    /**
     * Returns the values of the specified subtag.
     *
     * @param  string      $key     The subtag $key to retrieve
     * @param  string|null $default The default value
     * @return array|null  An indexed array of values, or null
     */
    public function getSubTagValues($key, array $default=null)
    {
        $tags = $this->extractSubTags();
        $k = array_search($key, $tags['keys']);

        return false === $k ? $default : explode(',', $tags['values'][$k]);
    }

    /**
     * Returns the boolean value of the specified subtag.
     *
     * @param  string       $key     The subtag $key to retrieve
     * @param  string|null  $default The default value
     * @return boolean|null
     */
    public function getSubTagBool($key, $default=null)
    {
        $tags = $this->extractSubTags();
        $k = array_search($key, $tags['keys']);

        $value = $k === false ? ( $default ? (bool) $default : null)  : $tags['values'][$k];

        return null === $value ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Extracts all the subtags.
     *
     * @return array An associative array
     */
    public function extractSubTags()
    {
        // if (null === $this->subtags_extract) {
            $lines = $this->entity->getAnnotationValue($this->annotation);

            if (!is_array($lines)) {
                $lines = array($lines);
            }

            foreach ($lines as $line) {
                preg_match_all(
                    '/(?P<keys>[^=\s]+)=(?P<values>[^=\s]+)/i',
                    $line,
                    $this->subtags_extract
                );
            }
        // }
        return $this->subtags_extract;
    }

    /**
     * Sets this plugin entity.
     *
     * @param  Entity $entity
     * @return void
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Sets this plugin entity.
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Sets the annotation string for this plugin.
     *
     * @return void
     */
    public function setAnnotation($string)
    {
        $this->annotation = $string;
    }

}
