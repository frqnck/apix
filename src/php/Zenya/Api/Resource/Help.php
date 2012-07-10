<?php

namespace Zenya\Api\Resource;

use Zenya\Api\Reflection,
    Zenya\Api\Entity,
    Zenya\Api\Server,
    Zenya\Api\Router;

/**
 * Help resource
 *
 * The Help resource provides in-line referencial to the API resources and methods.
 * By specify a resource and method you can narrow down to specific section.
 *
 * @api_version 1
 */
class Help
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        #$this->server = $server;
        $this->verbose = isset($_REQUEST['verbose'])?:false;
    }

    /**
     * Outputs help info for a resource entity (GET request).
     * Filters can be use to narrow down to a specified method.
     *
     * @param  string $path    A path to a resource entity to retrieve
     * @param  array  $filters An array of filters (optional)
     * @return array
     *
     * @api_link GET /help/path/to/entity
     */
    public function onRead($path, array $filters=null)
    {
        $path = preg_replace('@^.*help(\.\w+)?@i', '', $this->server->request->getUri());
        $entity = $this->server->resources->get($path);

        return array(
            $path => $this->_getHelp($entity, $filters)
        );
    }

    /**
     * Outputs help info for a resource entity (OPTIONS request).
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
     * @param  string $path    A path to a resource entity to retrieve
     * @param  array  $filters An array of filters (optional)
     * @return array
     *
     * @api_links OPTIONS /path/to/entity
     * @api_links OPTIONS /*
     */
    public function onHelp(Entity $entity, Server $server, array $filters=null)
    {

print_r($server);exit;

echo 'whole!! hte';

        // apply to the whole server
        if ($this->server->getRoute()->getName() == '/*') {
echo 'whole!!';
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
            // one entity
            return $this->_getHelp($entity, $filters);


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
     * Get an entity documentaion.
     *
     * @param  string $entity
     * @param  array  $filters
     * @return array  array
     */
    private function _getHelp(Entity $entity, array $filters=null)
    {
        // $man = $this->getParam('resource');
        // $resource = Zenya_Api_Resource::getInternalAppelation($man);
        // $help = new Zenya_Api_ManualParser($resource, $man, 'api_');
        // $this->_output = $help->toArray();

        if ($this->verbose) {
            return array(
                'TODO'          => 'Verbose mode',
                'end-user-doc'  => $entity->getDocs()
            );
        }

        return $entity->getDocs();
    }

}
