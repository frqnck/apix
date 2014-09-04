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

abstract class PluginAbstract implements \SplObserver
{

    /**
     * Holds the hook'ing array.
     * @var array
     */
    public static $hook = array();

    /**
     * Holds a plugin's adapter.
     * @var  closure|object
     */
    protected $adapter = null;

    /**
     * Holds an array of plugin's options.
     * @var  array
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param mix $options Array of options
     */
    public function __construct($options=null)
    {
        $this->setOptions($options);

        if (isset($this->options['adapter'])) {
            $this->setAdapter($this->options['adapter']);

            if ( isset(static::$hook) && isset(static::$hook['interface'])) {
                self::checkAdapterClass(
                    $this->adapter,
                    static::$hook['interface']
                );
            }
        }
    }

    /**
     * Checks the plugin's adapter comply to the provided class/interface.
     *
     * @param  object            $adapter
     * @param  object            $class
     * @throws \RuntimeException
     * @return true
     */
    public static function checkAdapterClass($adapter, $class)
    {
        if (!is_subclass_of($adapter, $class)) {
            throw new \RuntimeException(
                sprintf('%s not implemented.', $class)
            );
        }

        return true;
    }

    /**
     * Sets and merge the defaults options for this plugin.
     *
     * @param mix $options Array of options if it is an object set as an adapter
     */
    public function setOptions($options=null)
    {
        if (null !== $options) {
            if (is_object($options)) {
                $options = array('adapter' => $options);
            }
            $this->options = $options+$this->options;
        }
    }

    /**
     * Gets this plugin's options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets this plugin's adapter.
     *
     * @param closure|object $adapter
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $this->adapter = new $adapter();
        } else {
            $this->adapter = $adapter instanceof \Closure
                                ? $adapter()
                                : $adapter;
        }
    }

    /**
     * Gets this plugin's adapter.
     *
     * @return mix
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Just a shortcut for now. This is TEMP and will be moved elsewhere!
     * TODO: TEMP to refactor
     * @codeCoverageIgnore
     */
    public function log($msg, $context=null, $level='debug')
    {
        if (defined('DEBUG') && !defined('UNIT_TEST')) {
            if(is_array($context)) $context = implode(', ', $context);

            $str = sprintf('%s %s (%s)', get_class($this), $msg, $context);
            error_log( $str );
        }
    }

}
