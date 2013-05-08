<?php
namespace Apix\Plugin;

abstract class PluginAbstract implements \SplObserver
{

    protected $adapter = null;
    protected $options = array();

    /**
     * Constructor
     *
     * @param mix $options Array of options
     */
    public function __construct($options=null)
    {
        $this->setOptions($options);

        if (isset($this->options['adapter'])) {
            $this->setAdapter($this->options['adapter']);

            if ( isset(static::$hook) && isset(static::$hook['interface'])) {
                $this->checkAdapterClass(
                    $this->adapter,
                    static::$hook['interface']
                );
            }
        }
    }

    /**
     * Checks the adapter comply to a class/interface
     *
     * @param  object            $adapter
     * @throws \RuntimeException
     * @return true
     */
    public function checkAdapterClass($adapter, $class)
    {
        if (!is_subclass_of($adapter, $class)) {
            throw new \RuntimeException(
                sprintf('%s not implemented.', $class)
            );
        }

        return true;
    }

    /**
     * Sets and merge the defaults options
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
     * Gets the options
     *
     * @return mix
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the adapter
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
    }

    /**
     * Gets the adapter
     *
     * @return mix
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Log shortcut
     * TODO: refactor
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
