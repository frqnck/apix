<?php
namespace Apix\Plugin;

use Apix\Entity;

abstract class PluginAbstractEntity extends PluginAbstract
{
    protected $annotation;

    protected $entity;

    private $subtags_extract = null;

    /**
     * Returns the value of the specifified subtag
     *
     * @param  string      $key     The $key to retrieve
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
     * Extracts all the subtags
     *
     * @return array An associative array
     */
    public function extractSubTags()
    {
        if (null === $this->subtags_extract) {
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
        }

        return $this->subtags_extract;
    }

    /**
     * Sets this plugin entity
     *
     * @param  Entity $entity
     * @return void
     */
    public function setEntity(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Sets this plugin entity
     *
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }

}
