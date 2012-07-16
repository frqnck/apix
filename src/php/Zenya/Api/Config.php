<?php

namespace Zenya\Api;

class Config #extends \Pimple
{

    public $config = array();

    /**
     * TEMP: Holds the injected array.
     * @var array
     */
    private $injected;

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
                $this->setConfig($config);
                break;

            case is_string($config):
                // add from a user file
                $config = $this->getConfigFromFile($config);

                $this->setConfig($config);

                break;

            default:
                // TODO: maybe try from the user dir?
                // $file = getenv('HOME') . '/.zenya/config.php';

                // TEMP: add the distribution file
                // TODO: add the distribution file
                $file = realpath(__DIR__ . '/../../../data/config.dist.php');
                $config = $this->getConfigFromFile($file);

                $this->setConfig($config);
        }
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
            throw new \RuntimeException(sprintf('The "%s" config file does not exist.', $file), 5000);
        }

        $config = require $file;
        if (null === $config || !is_array($config)) {
            throw new \RuntimeException(sprintf('The "%s" config file must return an array.', $file), 5001);
        }

        return $config;
    }

    /**
     * Sets the config property and merge the defaults.
     *
     * @param array $config=null
     */
    public function setConfig(array $config)
    {
        $this->config = $config;//+$this->getConfigDefaults();
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
     * Returns the specified config value using its index key.
     * If the index key is not set then it will return the whole config property.
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
       throw new \InvalidArgumentException( sprintf('Config for "%s" does not exists.', $key) );
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
       throw new \InvalidArgumentException( sprintf('Default config for "%s" does not exists.', $key) );
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
        $config = isset($this->config['default'][$type])
                    ? $this->config[$type]+$this->getDefault($type)
                    : $this->config[$type];

        if (is_null($key)) {
            return $config;
        } elseif (isset($config[$key])) {
            return $config[$key];
        }
       throw new \RuntimeException( sprintf('"%s" does not exists in %s.', $key, $type) );
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
     * Returns the specified plugin (or all if unspecified).
     *
     * @param string $key=null The plugin key to retrieve.
     * @see     self::retrieve
     */
    public function getListeners($key=null)
    {
        return $this->retrieve('listeners', $key);
    }

    /**
     * Returns the specified service (or all if unspecified).
     *
     * @param string $key=null The service key to retrieve.
     * @see self::retrieve
     * @return mixed Generally should return a callback
     */
    public function getServices($key=null)
    {
        $service = $this->retrieve('services', $key);

        if (is_callable($service)) {
            return $service();
        }

        return $service;
    }

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

    /**
     * TEMP: Returns the default configuration.
     * TODO: should use 'config.dist.php'
     *
     * @return array
     */
    public function getConfigDefaults()
    {
        return array(
            'api_realm'     => 'Zenya',
            'api_version'   => '1.0',

            // output
            'output_rootNode'  => 'zenya',
            'output_sign'      => true,
            'output_debug'     => true,

            // routing
            'routing' => array(
                'route_prefix'      => '@^(/index(\d)?.php)?/api/v(\d*)@i', // regex
                'default_format'    => 'json',
                'formats'           => array('json', 'xml', 'html', 'php', 'jsonp'),
                // output format negociations
                'controller_ext'    => true, // true or false (e.g. resource.json)
                'format_override'   => isset($_REQUEST['format']) ? $_REQUEST['format'] : false,
                'http_accept'       => true, // true or false
            ),

            // resources
            'resources' => array(),

            'resources_default' => array(
                // OPTIONS
                'help' => array(
                    'controller' => array(
                        'name' => __NAMESPACE__ . '\Resource\Help',
                        'args' => null
                    ),
                ),
                // HEAD
                'test' => array(
                    'controller' => array(
                        'name' => __NAMESPACE__ . '\Resource\Test',
                        'args' => null
                    ),
                )
            ),

            // listeners
            'listeners' => array(),

            'listeners_default' => array(
                'server' => array(
                    // pre-processing stage
                    'early' => array(
                        #'Zenya\Api\Listener\Mock',
                        #'Zenya\Api\Listener\BodyData',
                    ),
                    // post-processing stage
                    'late'=>array(
                    ),
                    // errors and exceptions
                    'exception' => array(
                        #'Zenya\Api\Listener\Log' => array('php://output'),
                    )
                ),
                'entity' => array(
                    'early' => array(),
                    // post-processing stage
                    'late'=>array(
                        #'Zenya\Api\Listener\Mock',
                        #'Zenya\Api\Listener\Log' => array('php://output'),
                    ),
                ),
                'response' => array(),
            ),

            // services
            'services' => array(),
            #'services_default' => array()

        );
    }

}
