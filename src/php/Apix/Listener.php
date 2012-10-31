<?php

namespace Apix;

class Listener implements \SplSubject, \IteratorAggregate, \Countable
{
    /**
     * @var array of SplObjectStorage
     */
    protected $observers = array();

    /**
     * @var array of SplObjectStorage
     */
    protected $listeners = array();

     /**
     * Attaches a new observer
     *
     * @param SplObserver $observer Any object implementing SplObserver
     */
    public function attach(\SplObserver $observer)
    {
        foreach ($this->observers as $attached) {
            if ($attached === $observer) {
                return;
            }
        }
        $this->observers[] = $observer;
    }

    /**
     * Detaches an existing observer
     *
     * @param SplObserver $observer any object implementing SplObserver
     */
    public function detach(\SplObserver $observer)
    {
        foreach ($this->observers as $key => $attached) {
            if ($attached === $observer) {
                unset($this->observers[$key]);
                #return;
            }
        }
    }

    /**
     * Notifies all the observers
     *
     * @return void
     */
    public function notify()
    {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * IteratorAggregate
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->observers;
    }

    /**
     * Countable
     *
     * @return integer
     */
    public function count()
    {
        return count($this->observers);
    }

    /**
     * Add listeners
     *
     * @param array $level
     */
    public function setListenersLevel($level, array $periods)
    {
        $this->listeners[$level] = $periods;
    }

    /**
     * Get a level of listeners.
     *
     * @param string $key 
     */
    public function getListenersLevel($key)
    {
        if(!isset($this->listeners[$key])) {
            // retrieve from config
            $this->listeners[$key] = Config::getInstance()->getListeners($key);
        }
        return $this->listeners[$key];
    }

    /**
     * Adds all the listeners for the current level/type.
     *
     * @param string $level     Representing the current level/stage.
     * @param string $type      Representing the current type/period.
     */
    public function addAllListeners($level, $type=null)
    {
        //$level = get_called_class();
        
        $listeners = $this->getListenersLevel($level);

        $stage = is_null($type)
            ? $listeners
            : $listeners[$type];

        foreach ($stage as $plugin => $args) {
            $this->attach(
                $this->callPlugin($plugin, $args)
            );
        }

        ###$this->setNotice($type);
    }

    /**
     * Call the plugin.
     *
     * @param mix $plugin
     * @param mix $args
     */
    private function callPlugin($plugin, $args)
    {
        if (is_int($plugin)) {
            return $args instanceof \Closure ? $args() : new $args();
        }

        // if( !class_exists($plugin) || !is_callable($plugin) ) { 
        //     throw new \BadMethodCallException("Plugin \"{$plugin}\" not available.");
        // }

        return new $plugin($args);
    }

}