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

namespace Apix;

// Pluggable
class Listener implements \SplSubject, \IteratorAggregate, \Countable
{
    /**
     * @var array of SplObjectStorage
     */
    protected $observers = array();

    /**
     * @var array of plugins
     */
    protected static $plugins = array();

     /**
     * Attaches a new observer
     *
     * @param  \SplObserver $observer Any object implementing SplObserver
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
     * @param \SplObserver $observer Any object implementing SplObserver
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
     * Gets the IteratorAggregate
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
     * Sets the plugins at the specified level
     *
     * @param  array $key     The level key
     * @param  array $plugins An array of plugins
     * @return void
     */
    public function setPluginsAtLevel($key, array $plugins)
    {
        self::$plugins[$key] = $plugins;
    }

    /**
     * Gets the plugins at the specified level
     *
     * @param  string      $key
     * @return array|false An array of plugins
     */
    public function getPluginsAtLevel($key)
    {
        if (!isset(self::$plugins[$key])) {
            return false;
        }

        return self::$plugins[$key];
    }

    /**
     * Calls the plugins of the specified level and type
     *
     * @param string $level
     * @param string $type
     */
    public function hook($level, $type)
    {
        $plugins = $this->getPluginsAtLevel($level);
        if (isset($plugins[$type])) {
            foreach ($plugins[$type] as $plugin => $args) {
                $obs = $this->callPlugin($plugin, $args);
                $this->attach($obs);
                $obs->update($this);
            }
        }
   }

    /**
     * Calls and executes the plugin
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

        $args = $args instanceof \Closure
                ? $args()
                : $args;

        return new $plugin($args);
    }

    /**
     * Load the specified plugin
     *
     * @param  string           $name The plugin name to load
     * @return boolean          True on success, false otherwise
     * @throws RuntimeException If the plugin does not exists
     * @throws DomainException  If the plugin does not have a $hook property.
     */
    public function loadPlugin($key, $mix)
    {
       $name = is_int($key) ? $mix : $key;

       if ( !class_exists($name) ) {
            throw new \RuntimeException(
                sprintf('Plugin class "%s" not found', $name)
            );
        }

        if (!isset($name::$hook)) {
            throw new \DomainException(
                sprintf(
                    'Plugin class "%s" does not have the expected '
                    . 'static \'hook\' property',
                    $name
                )
            );
        }

        $level = $name::$hook[0];
        $type = $name::$hook[1];

        self::$plugins[$level][$type][$key] = $mix;

        return true;
    }

    /**
     * Load all the plugins
     *
     * @param array $plugins The array of plugins to load
     */
    public function loadPlugins(array $plugins)
    {
        foreach ($plugins as $key => $mix) {
            $this->loadPlugin($key, $mix);
        }
    }

}
