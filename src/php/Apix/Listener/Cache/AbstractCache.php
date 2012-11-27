<?php
namespace Apix\Listener\Cache;

abstract class AbstractCache implements Adapter
{
    protected $adapter;

    protected $options = array(
        'key_prefix' => '[apixKey] ', // Caching prefix for keys.
        'tag_prefix' => '[apixTag] ', // Caching prefix for tags
    );

    /**
     * Constructor.
     *
     * @param Cache\Adapter $adapter
     * @param array $options Array of options.
     */
    public function __construct($adapter=null, array $options=null)
    {
        $this->adapter = $adapter;

        if(null !== $options) {
	        $this->options = $options+$this->options;
    	}
    }

    /**
     * Returns a prefixed and sanitased cache id.
     *
     * @param  string $key  The base key to prefix.
     * @return string
     */
    public function mapKey($key)
    {
        return $this->sanitise($this->options['key_prefix'] . $key);
    }

    /**
     * Returns a prefixed and sanitased cache tag.
     *
     * @param  string $tag  The base tag to prefix.
     * @return string
     */
    public function mapTag($tag)
    {
        return $this->sanitise($this->options['tag_prefix'] . $tag);
    }

    /**
     * Returns a sanitased string keying/tagging purpose.
     *
     * @param  string $key   The string to sanitise.
     * @return string
     */
    public function sanitise($key)
    {
        return $key;
        // return str_replace(array('/', '\\', ' '), '_', $key);
    }

}