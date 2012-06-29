<?php

namespace Zenya\Api\Resource;

use Zenya\Api\Reflection;
use Zenya\Api\Entity;
use Zenya\Api\Server;
use Zenya\Api\Router;

/**
 * Help
 *
 * The Help resource provides in-line referencial to the API resources and methods.
 * By specify a resource and/or method you can narrow down to specific section.
 *
 * @api_version 1
 */
class Help
{
    /**
     * Constructor
     *
     * @param  Server $server The resource's to retrieve
     * @return void
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Help (GET)
     *
     * Retrieve help for a specified resource/method.
     *
     * @param  string $resource The resource's to retrieve
     * @param  string $method   The resource's method to focus upon (optional)
     * @param  array  $filters  An array of filters (optional)
     * @return array
     *
     * @api_link GET /help/resource/method/filters
     */
    public function onRead($resource, $http_method=null, array $filters=null)
    {
        return array(
            $resource => $this->_getHelp($resource, $http_method, $filters)
        );
    }

    /**
     * Help
     *
     * The OPTIONS method represents a request for information about the
     * communication options available on the request/response chain
     * identified by the Request-URI. This method allows the client to determine
     * the options and/or requirements associated with a resource,
     * or the capabilities of a server, without implying a resource action or
     * initiating a resource retrieval.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
     *
     * @param  string $resource The resource's to retrieve
     * @param  string $method   The resource's method to focus upon (optional)
     * @param  array  $filters  An array of filters (optional)
     * @return array
     *
     * @api_links OPTIONS /resource/method/filters
     * @api_links OPTIONS /\*\/method/filters
     */
    public function onHelp(Entity $entity, array $filters=null)
    {
        // apply to the whole server
        if ($entity->route->path == '/*') {

            // return the whole api doc
            $doc = array();
            foreach ($this->server->getResources() as $key => $entity) {
                $doc[$resource] =  $this->_getHelp($entity, $filters);
            }

            // // set Content-Type (negotiate or default)
            // if( $request->hasHeader('CONTENT_LENGTH')
            //     || $request->hasHeader('TRANSFER_ENCODING')
            // ) {
            //     // TODO: process the $this->server->body!
            //     // @expect   client request  has an entity-body (indicated by Content-Length or Transfer-Encoding) then client's Content-Type must be set.
            //     return array('doc'=>'Todo: return all the resource doc as per CONTENT_LENGTH and/or TRANSFER_ENCODING');
            // }

        } else {

            $doc = $this->_getHelp($entity, $filters);
            return $doc;


            // specific to just one resource.
            if ($resource == 'help') {
                // helps help itslef
                $doc = $this->_getHelp($this->server->route, $http_method, $filters);
            } else {
                // helps specified resource



                $doc = array($resource => $this->_getHelp($this->server->route, $http_method, $filters));
            }

            // // A server that does not support such an extension MAY discard the request body.
            // if ( null === $this->server->request->getRawBody()) {
            //     $this->server->response->setHeader('Content-Length', 0);
            // }
            // $this->server->response->setHeader('Allow',
            //     implode(', ', $this->server->resource->getMethodKeys())
            // );

        }

        return $doc;
    }

    /**
     * Retrieve help for a resource
     *
     * @param  string $resource
     * @param  string $method
     * @param  array  $filters
     * @return mixed  array or string on error
     * @access  private
     */
    private function _getHelp(Entity $entity, array $filters=null)
    {
        // $man = $this->getParam('resource');
        // $resource = Zenya_Api_Resource::getInternalAppelation($man);
        // $help = new Zenya_Api_ManualParser($resource, $man, 'api_');
        // $this->_output = $help->toArray();

        $actions = $entity->getActions();

        $reflection = new Reflection($entity->parseDocs());
        return $reflection->getDocs($actions);
    }

}
