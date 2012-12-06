<?php

namespace Apix;

//Pluggable
class Listener implements \SplSubject, \IteratorAggregate, \Countable
{
    /**
     * @var array of SplObjectStorage
     */
    protected $observers = array();

    /**
     * @var array
     */
    protected $listeners = array();

     /**
     * Attaches a new observer
     *
     * @param SplObserver $observer Any object implementing SplObserver
     * @return void|false
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

        // echo '<pre>';print_r($this->listeners[$key]);
        return $this->listeners[$key];
    }

    /**
     * Calls the listeners of the current level and type.
     *
     * @param string $level     Representing the current level/stage.
     * @param string $type      Representing the current type/period.
     */
    public function hook($level, $type=null)
    {
        #if($level == null) echo $level = get_called_class();

        $plugins = $this->getListenersLevel($level);
#        if(null !== $plugins) {
            $stage = is_null($type)
                ? $plugins
                : $plugins[$type];

            foreach ($stage as $plugin => $args) {
                $obs = $this->callPlugin($plugin, $args);
                $this->attach($obs);
                $obs->update($this);
            }
#        }
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
            return $args instanceof \Closure
                    ? $args()
                    : new $args();
        }

        // if( !class_exists($plugin) || !is_callable($plugin) ) {
        //     throw new \BadMethodCallException("Plugin \"{$plugin}\" not callable.");
        // }

        $args = $args instanceof \Closure
                ? $args()
                : $args;


        // if(is_array($args)) {
        //     #return call_user_func_array($plugin, extract($args));

        //     $reflect  = new \ReflectionClass($plugin);
        //     return $reflect->newInstanceArgs($args);
        // }

        return new $plugin($args);
    }

    /**
    * Load all the plugins
    *
    * @param array $plugins Array of plugins to load.
    */
    public function loadPlugins(array $plugins)
    {
        foreach ($plugins as $key => $mix) {
          $this->loadPlugin($key, $mix);
        }
    }

    protected $plugins = array();

    /**
    * Load the specified plugin
    *
    * @param string $name The plugin name to load.
    * @return boolean True on success, false if not loaded or failure.
    */
    public function loadPlugin($key, $mix)
    {
        // plugin already loaded
        $name = is_int($key) ? $mix : $key;

        if (isset($this->plugins[$name])) {
            return true;
        }

        if(!isset($name::$hook)) {
            return false;
        }
        $hook = $name::$hook;

        // $args = $args instanceof \Closure
        //         ? $args()
        //         : $args;

        // if( is_callable($mix) ) {
        //      $mix = $mix();
        // }

        Config::getInstance()->addListener(
            $key,
            $mix,
            $hook[0],   // level
            $hook[1]    // type
        );

        // $this->listeners[$level][$type][] = $name;

//         // $plugin = new $name($this);
//         // check inheritance...
//         if (is_subclass_of($plugin, 'Apix\Listener\AbstractListener'))
//         {
//            // $plugin->init();
// echo '+ ' . $level;
//             return true;
//         } else {
//             // throw new \Exception(
//             //     sprintf('%s is not a working plugin class found'), $name)
//             // );
//         echo 'Error';
//         }

        // return false;
    }

}