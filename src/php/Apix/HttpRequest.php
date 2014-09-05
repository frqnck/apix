<?php
namespace Apix;

use Apix\Request,
    Apix\Input\InputInterface,
    Apix\Input\Xml,
    Apix\Input\Json;

class HttpRequest extends Request
{

    /**
     * TEMP: The singleton instance.
     * @var Request
     */
    private static $instance = null;

    /**
     * List of supported input formats.
     * @var array
     */
    protected $formats = array('post', 'json', 'xml');

    /**
     * TEMP: Returns as a singleton instance.
     *
     * @return Request
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
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
     * Returns the format from an HTTP context.
     *
     * @param  string $context The context to extract the format from.
     * @return string The extracted format.
     */
    public static function getFormat($context)
    {
        switch (true) {
            // text/html
            case (strstr($context, '/html')):
                return 'html';

            // application/json
            case (strstr($context, '/json')):
                return 'json';

            // text/xml, application/xml
            case (strstr($context, '/xml')
                && (!strstr($context, 'html'))):
                return 'xml';

            default:
                return null;
        }
    }

    /**
     * Returns the format from an HTTP Accept.
     *
     * @return string The output format
     */
    public function getAcceptFormat()
    {
        if ($this->hasHeader('HTTP_ACCEPT')) {
            $accept = $this->getHeader('HTTP_ACCEPT');

            if (!$format = self::getFormat($accept)) {
                // 'application/javascript'
                $format = strstr($accept, '/javascript') ? 'jsonp' : null;
            }
        }

        return isset($format) ? $format : false;
    }

    /**
     * Get & parse the request body-data.
     *
     * @param  boolean $assoc Wether to convert objects to associative arrays or not.
     * @return array
     * @link http://www.w3.org/TR/html401/interact/forms.html#h-17.13.4
     * @link http://www.w3.org/TR/html401/references.html#ref-RFC2388
     */
    public function getBodyData($assoc=true)
    {
        if ($this->hasBody() && $this->hasHeader('CONTENT_TYPE')) {
            $ctx = $this->getHeader('CONTENT_TYPE');

            if (
                in_array('post', $this->formats)
                // application/x-www-form-urlencoded
                && strstr($ctx, '/x-www-form-urlencoded')
                // TODO: shall we do multipart/form-data too?
            ) {
                // handles by PHP already. No need to parse the body.
                return $this->getParams();
            } elseif ($format = self::getFormat($ctx)) {
                if (in_array(strtolower($format), $this->formats)) {
                    $class = __NAMESPACE__ . '\Input\\' . ucfirst($format);
                    $input = new $class();

                    return $input->decode($this->getBody(), $assoc);
                    #$this->setParams($r); // TODO: maybe set as request params?
                }
            }

            return null;
        }
    }

    /**
     * Sets the input formats.
     *
     * @return void
     */
    public function setFormats(array $formats)
    {
        $this->formats = $formats;
    }

}
