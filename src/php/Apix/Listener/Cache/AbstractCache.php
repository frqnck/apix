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
    public function __construct(stdClass $adapter=null, array $options=null)
    {
        $this->adapter = $adapter;
        
        if(null !== $options) {
	        $this->options = $options+$this->options;
    	}
    }

    /**
     * Returns a prefixed and sanitased cache id.
     *
     * @param  string $id The cache Id.
     * @return string
     */
    public function mapKey($id)
    {
        return $this->sanitise($this->options['key_prefix'] . $id);
    }

    /**
     * Returns a prefixed and sanitased cache tag.
     *
     * @param  string $tag The cache tag.
     * @return string
     */
    public function mapTag($tag)
    {
        return $this->sanitise($this->options['tag_prefix'] . $tag);
    }

    /**
     * Returns a sanitased string for id/tagging purpose.
     *
     * @param  string $id The string to sanitise.
     * @return string
     */
    public function sanitise($id)
    {
        return $id;
        // return str_replace(array('/', '\\', ' '), '_', $id);
    }

}