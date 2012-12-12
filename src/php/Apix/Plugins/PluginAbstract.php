<?php
namespace Apix\Plugins;

abstract class PluginAbstract implements \SplObserver
{

    protected $adapter = null;
    protected $options = array();

    /**
     * Constructor
     *
     * @param mix $options Array of options if it is an object set as an adapter
     */
    public function __construct($options=null)
    {
        $this->setOptions($options);

        if (isset($this->options['adapter'])) {
            $this->setAdapter($this->options['adapter']);
        }
    }

    /**
     * Sets and merge with the plugin defaults options
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
     * Gets the plugin options
     *
     * @return mix
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the plugin adapter
     *
     * @param closure|object $adapter
     */
    public function setAdapter($adapter)
    {
        if (is_string($adapter)) {
            $this->adapter = new $adapter;
        } else {
            $this->adapter = $adapter instanceof \Closure
                                ? $adapter()
                                : $adapter;
        }

        // Checks an adapter comply to a hook interface
        if(
            isset(static::$hook)
            && isset(static::$hook['interface'])
            && !is_subclass_of($this->adapter, static::$hook['interface'])
        ) {
            throw new \RuntimeException(
                sprintf('%s not implemented.', static::$hook['interface'])
            );
        }
    }

    /**
     * Gets the plugin adapter
     *
     * @return mix
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Log shortcut
     */
    public function log($msg, $ref=null, $level='debug')
    {
        if (defined('DEBUG') && !defined('UNIT_TEST')) {
            $str = sprintf('%s %s (%s)', get_class($this), $msg, $ref);
            error_log( $str );
        }
    }

}
