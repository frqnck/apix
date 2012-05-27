<?php

namespace Zenya\Api\Resource;

class Help #extends ResourceAbstract
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

    public function __construct($self)
    {
        // instantiate
        #if ($route->path == '/*' && $route->method == 'OPTIONS') {
        #$route->name = '*';
    }

    /**
     * HTTP OPTIONS: Help action handler
     *
     * The OPTIONS method represents a request for information about the
     * communication options available on the request/response chain
     * identified by the Request-URI. This method allows the client to determine
     * the options and/or requirements associated with a resource,
     * or the capabilities of a server, without implying a resource action or
     * initiating a resource retrieval.
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
     *
     * @todo	TODO
     *
     * @expect	client request  has an entity-body (indicated by Content-Length or Transfer-Encoding) then client's Content-Type must be set.
     *
      * @cacheable false
     */
    public function helpApiResource($resource, $method=null, $params=null)
    {



        $out = array('HELP'=>array(
            'user resource'=> $resource,
            'user method'=> $method,
            'user params' => $params
            )
        );
#        $out .= new \Zenya\Api\Resource\RefMethod($resource, $method, 'api_');

        return $out;

        $request = $this->server->request;

        #Server::d($request);

        // apply to the whole server
        if ($this->server->route->path == '/*') {
                // set Content-Type (negotiate or default)

            if( $request->hasHeader('CONTENT_LENGTH')
                || $request->hasHeader('TRANSFER_ENCODING')
            ) {
                // TODO: process the $this->server->body!
                return array('doc'=>'All the resource doc');
            }

            // return the whole API doc.
            return array('doc'=>'All the resources...');

        } else {
            // specific to one resource
            header('Allow: ' . implode(', ', $refClass->getMethods()), true);

            // A server that does not support such an extension MAY discard the request body.
            if ( null === $request->getRawBody()) {
                header('Content-Length: 0');
            }

            /*
                $man = $this->getParam('resource');
                $resource = Zenya_Api_Resource::getInternalAppelation($man);
                $help = new Zenya_Api_ManualParser($resource, $man, 'api_');
                $this->_output = $help->toArray();
            */

            return array('doc'=>'a specific resource doc');
        }

        return array('Help Handler, handles HTTP OPTIONS method');
    }

    public function updateApiResource(array $params)
    {
        $this->results = array('method'=>__METHOD__, 'params'=>$params);
    }

}
