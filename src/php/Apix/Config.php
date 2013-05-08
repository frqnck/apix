<?php

namespace Apix;

class Config #extends Di
{
    /**
     * Holds the config array.
     * @var array
     */
    public $config = array();

    /**
     * TEMP: Holds the singleton instance.
     * @var Config
     */
    private static $instance = null;

    /**
     * TEMP: Returns as a singleton instance.
     *
     * @return Config
     */
    public static function getInstance($config=null, $skip=false)
    {
        if (null === self::$instance) {
            self::$instance = new self($config, $skip);
        }

        return self::$instance;
    }

    /**
     * TEMP: disalow cloning.
     *
     * @codeCoverageIgnore
     */
    final private function __clone() {}

    /**
     * Initialises and sets the config property.
     *
     * @return Config
     */
    public function __construct($config=null)
    {
        switch (true) {

            case is_array($config):
                // add from user provided array
                break;

            case is_string($config):
                // add from a user file
                $config = $this->getConfigFromFile($config);
                break;

            default:

                // add from the HOME dir
                // $file = getenv('HOME') . '/.apix/config.php';
                // if (file_exists($file)) {
                //     $config = $this->getConfigFromFile($file);
                // }

                // default to the distribution file
                $config = null;
        }

        $this->setConfig($config);
    }

    /**
     * Sets the config array from a file.
     *
     * @param  string            $file The full path to a configuration file.
     * @throws \RuntimeException
     * @return void;
     */
    public function getConfigFromFile($file)
    {
        if (!is_file($file)) {
            throw new \RuntimeException(
                sprintf('The "%s" config file does not exist.', $file),
                5000
            );
        }

        $config = require $file;
        if (null === $config || !is_array($config)) {
            throw new \RuntimeException(
                sprintf('The "%s" config file must return an array.', $file),
                5001
            );
        }

        return $config;
    }

    /**
     * Sets the config property and merge the defaults.
     *
     * @param array $config=null
     */
    public function setConfig(array $config=null)
    {
        $defaults = $this->getConfigDefaults();
        $this->config = null === $config ? $defaults : $config+$defaults;
    }

    /**
     * Returns the config array.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Returns the specified config value using its index key. If the index key
     * is not set then it will return the whole config property.
     *
     * @param  string                    $key=null The key to retrieve.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function get($key=null)
    {
        if (is_null($key)) {
            return $this->getConfig();
        } elseif (isset($this->config[$key])) {
            return $this->config[$key];
        }

        throw new \InvalidArgumentException(
            sprintf('Config for "%s" does not exists.', $key)
        );
    }

    /**
     * Sets as new a mixed value to a specified key.
     *
     * @param string $key The key to set.
     * @param mixed  $mix The mixed value to set.
     */
    public function set($key, $mix)
    {
        $this->config[$key] = $mix;
    }

    /**
     * Adds the mixed value to a specified key.
     *
     * @param string $key The key to add to.
     * @param mixed  $mix The mixed value to add.
     */
    public function add($key, $mix)
    {
        $this->config[$key][] = $mix;
    }

    /**
     * Returns the specified default config value using its index key.
     * If the index key is not set then it will return the whole default config property.
     *
     * @param  string                    $key=null The key to retrieve.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getDefault($key=null)
    {
        if (is_null($key)) {
            return $this->config['default'];
        } elseif (isset($this->config['default'][$key])) {
            return $this->config['default'][$key];
        }

        throw new \InvalidArgumentException(
            sprintf('Default config for "%s" does not exists.', $key)
        );
    }

    /**
     * Returns a specified sub-array type from config.
     * If an index key is specified return the corresponding (mixed) value.
     *
     * @param  string                    $type     The sub-array type to retrieve.
     * @param  string                    $key=null A key to narrow the retrieval.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function retrieve($type, $key=null)
    {
        // TODO: will optimise this...
        $config = isset($this->config['default'][$type])
                    ? $this->config[$type]+$this->getDefault($type)
                    : $this->config[$type];

        if (null === $key) {
            return $config;
        } elseif (isset($config[$key])) {
            return $config[$key];
        }

        throw new \RuntimeException(
            sprintf('"%s" does not exists in "%s".', $key, $type)
        );
    }

    /**
     * Returns all the resources or as the specified.
     *
     * @param string $key=null The resource key to retrieve.
     * @see     self::retrieve
     */
    public function getResources($key=null)
    {
        return $this->retrieve('resources', $key);
    }

    /**
     * Returns the specified service -- or all if unspecified.
     *
     * @param string $key=null The service key to retrieve.
     * @see self::retrieve
     * @return mixed Generally should return a callback
     */
    public function getServices($key=null, $args=null)
    {
        $service = $this->retrieve('services', $key);

        return is_callable($service) ? $service($args) : $service;
    }

    /**
     * Sets the specified name, value as a service.
     *
     * @param  string $name The service name to set.
     * @param  mixed  $mix  The corresponding value to set.
     * @return void
     */
    public function setService($name, $mix)
    {
        $this->config['services'][$name] = $mix;
    }

    /**
     * TEMP: Returns the default configuration.
     * TODO: should use 'config.dist.php'
     *
     * @return array
     */
    public function getConfigDefaults()
    {
        // $file = realpath(__DIR__ . '/../../data/distribution/config.dist.php');
        $file = __DIR__ . '/../../data/distribution/config.dist.php';

        return $this->getConfigFromFile($file);
    }

    /* --- below obsolete --- */

    /**
     * TEMP: Holds the injected array.
     * @var array
     */
    private $injected;

    /**
     * TEMP: Sets/injects a key/value pair in the injected array.
     *
     * @param  string $key   An index key to set.
     * @param  mixed  $value The value to inject.
     * @return void
     */
    public function inject($key, $value)
    {
        $this->injected[$key] = $value;
    }

    /**
     * TEMP: Returns the specified injected key.
     *
     * @param  string $key The index key to retrieve.
     * @return mixed
     */
    public function getInjected($key)
    {
        return $this->injected[$key];
    }

}
