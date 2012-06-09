<?php

namespace Zenya\Api\Resource;

class Test
{
    /*
     * Another public var.
     */
    public $hello = 'World!!!';

    /*
     * A public var.
     */
    public $results = array();

    /*
     * A private var
     */
    protected $_protected = 'Checking protected var.';

    /*
     * A private var
     */
    protected $_private = 'Checking private var.';

    /**
     * Stores the names and methods requirements.
     *
     * @var array
     */
    protected $_requirements = array(
        'paramName' => array('GET'),
        array('PUT')
    );

    public function onRead(array $params)
    {
        $this->results = array(__METHOD__);
    }

    public function onUpdate(array $params)
    {
        $this->results = array('method'=>__METHOD__, 'params'=>$params);
    }

    /**
     * HTTP HEAD: test action handler
     *
     * The HEAD method is identical to GET except that the server MUST NOT return
     * a message-body in the response. The metainformation contained in the HTTP
     * headers in response to a HEAD request SHOULD be identical to the information
     * sent in response to a GET request. This method can be used for obtaining
     * metainformation about the entity implied by the request without transferring
     * the entity-body itself. This method is often used for testing hypertext links
     * for validity, accessibility, and recent modification.
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     *
     * @return null
       * @cacheable true
     */
    final public function onTest()
    {
        // identical to get without the body output.
        // shodul proxy to the get method!?
        return null; // MUST NOT return a message-body in the response
    }

}
