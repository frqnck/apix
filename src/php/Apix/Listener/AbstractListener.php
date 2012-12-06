<?php
namespace Apix\Listener;

abstract class AbstractListener implements \SplObserver
{

    protected $adapter = null;

    /**
     * Constructor.
     *
     * @param array $options Array of options, such as:
     *                       - 'adapter'
     */
    //public function __construct(Auth\Adapter $adapter, array $options=null)
    public function __construct($options=null)
    {
        if( is_object($options) ) {
            $options = array('adapter' => $options);
        }

        if(isset($this->options['adapter']) || isset($options['adapter'])) {
            $this->setAdapter($options);
        }

        $this->setOptions($options);
    }

    /**
     * Constructor.
     *
     * @param Cache\Adapter $adapter
     * @param array $options Array of options.
     */
    public function OFF__construct(Cache\Adapter $adapter, array $options=null)
    {
        $this->adapter = $adapter;

        $this->setOptions($options);
    }

    /**
     * Sets some options, merging with the plugin defaults.
     *
     * @param array $options
     */
    public function setOptions(array $options=null)
    {
        if(null !== $options) {
            $this->options = $options+$this->options;
        }
    }

    /**
     * Sets the plugin adapter.
     *
     * @param   mix     $adapter
     */
    public function setAdapter(array $options)
    {
        $adapter = isset($options['adapter'])
                    ? $options['adapter']
                    : null;

        if( null === $adapter) {
            throw new \RuntimeException(
                sprintf('%s missing an implement.', get_called_class())
            );
        }

        $adapter = $adapter instanceof \Closure
                ? $adapter()
                : $adapter;

        // todo: instantiate if string?! new $adapter

        if(!$adapter instanceof $this->options['adapter']) {
            throw new \RuntimeException(
                sprintf('%s not implemented.', $this->options['adapter'])
            );
        }

        $this->adapter = $adapter;
    }

    /**
    * Initialization method, needs to be implemented by the plugin itself
    */
    #abstract function init();

    function log($msg, $ref=null)
    {
        if(defined('DEBUG') && !defined('UNIT_TEST')) {
            $str = sprintf('%s %s (%s)', get_class($this), $msg, $ref);
            error_log( $str );
        }
    }


}