<?php
namespace Apix\Plugins;

abstract class PluginAbstract implements \SplObserver
{

    protected $adapter = null;
    protected $options = array();

    /**
     * Constructor.
     *
     * @param array $options List of options, if $options is an object,
     *                              set it as the plugin adapter.
     */
    public function __construct($options=null)
    {
        if ( is_object($options) ) {
            $options = array('adapter' => $options);
        }

        if (isset($this->options['adapter']) || isset($options['adapter'])) {
            $this->setAdapter($options);
        }

        $this->setOptions($options);
    }

    /**
     * Sets and merge with the plugin defaults options.
     *
     * @param array $options
     */
    public function setOptions(array $options=null)
    {
        if (null !== $options) {
            $this->options = $options+$this->options;
        }
    }

    /**
     * Gets the plugin options.
     *
     * @return mix
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Sets the plugin adapter.
     *
     * @param mix $adapter
     */
    public function setAdapter(array $options)
    {
        $adapter = isset($options['adapter'])
                    ? $options['adapter']
                    : null;

        // if (null === $adapter) {
        //     throw new \RuntimeException(
        //         sprintf('%s missing an implement.', get_called_class())
        //     );
        // }

        $adapter = $adapter instanceof \Closure
                ? $adapter()
                : $adapter;

        // todo: instantiate if string?! new $adapter

        if(
            isset($this->options['adapter'])
            && !$adapter instanceof $this->options['adapter']
        ) {
            throw new \RuntimeException(
                sprintf('%s not implemented.', $this->options['adapter'])
            );
        }

        $this->adapter = $adapter;
    }

    /**
     * Gets the plugin adapter.
     *
     * @return mix
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
    * Initialization method, needs to be implemented by the plugin itself
    */
    #abstract function init();

    public function log($msg, $ref=null)
    {
        if (defined('DEBUG') && !defined('UNIT_TEST')) {
            $str = sprintf('%s %s (%s)', get_class($this), $msg, $ref);
            error_log( $str );
        }
    }

}
