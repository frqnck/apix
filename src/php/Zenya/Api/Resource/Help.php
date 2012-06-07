<?php

namespace Zenya\Api\Resource;

use Zenya\Api\ReflectionClass as ReflectionClass;
use Zenya\Api\Server as Server;

class Help //extends Abstracted
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

    public function __construct(Server $server)
    {
        $this->server = $server;
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
    public function onHelp($name, $resource, $params=null)
    {
        Server::d(get_defined_vars());



        // apply to the whole server
        if ($this->server->route->path == '/*') {
            
            // set Content-Type (negotiate or default)
            if( $request->hasHeader('CONTENT_LENGTH')
                || $request->hasHeader('TRANSFER_ENCODING')
            ) {
                // TODO: process the $this->server->body!
                return array('doc'=>'Todo: return all the resource doc');
            }

            // return the whole API doc.

        } else {
            // specific to just one resource.
            $doc = new ReflectionClass($resource['class']);
            $doc->parseClassDoc();

            // TODO: set headers
            #$this->server->response->setHeader('Allow',
            #    implode(', ', $this->server->resource->getMethods())
            #);

            // A server that does not support such an extension MAY discard the request body.
            if ( null === $this->server->request->getRawBody()) {
                $this->server->response->setHeader('Content-Length', 0);
            }

            foreach($doc->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                $doc->parseMethodDoc($method->name);
            }
        }

        return $doc->getDocs();

        /*
            $man = $this->getParam('resource');
            $resource = Zenya_Api_Resource::getInternalAppelation($man);
            $help = new Zenya_Api_ManualParser($resource, $man, 'api_');
            $this->_output = $help->toArray();
        */
    }

}
