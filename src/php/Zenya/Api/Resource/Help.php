<?php

namespace Zenya\Api\Resource;

use Zenya\Api\Reflection,
    Zenya\Api\Entity,
    Zenya\Api\Server,
    Zenya\Api\Request,
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
        $this->verbose = isset($_REQUEST['verbose'])?$_REQUEST['verbose']:false;
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
    public function onRead(Server $server, $path, array $filters=null)
    {
        $path = preg_replace('@^.*help(\.\w+)?@i', '', $server->request->getUri());
        $server->route->setName($path);
        $entity = $server->resources->get($server->route);
        return array(
            $path => $this->getDocs($entity, $filters)
        );
    }

    /**
     * Help info for a resource entity (OPTIONS request).
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
    public function onHelp(Server $server, Entity $entity=null, array $filters=null)
    {
        // output the whole api doc
        if ($server->getRoute()->getName() == '/*' || null == $entity) {
            $server->route->setController('help');
            $doc = array();
            foreach ($server->resources->toArray() as $key => $entity) {
                if(!$entity->hasRedirect()) {
                    $doc[$key] = $this->getDocs($entity, $filters);
                }
            }

            // // set Content-Type (negotiate or default)
            // if(
            //      $request->hasHeader('CONTENT_LENGTH')
            //      || $request->hasHeader('TRANSFER_ENCODING')
            // ) {
            //     // @expect   client request has an entity-body (indicated by Content-Length or Transfer-Encoding) then client's Content-Type must be set.
            //     return array('doc'=>'Todo: return all the resource doc as per CONTENT_LENGTH and/or TRANSFER_ENCODING');
            // }

        } else {
            // one entity
            return $this->getDocs($entity, $filters);


            // specific to just one resource.
            if ($resource == 'help') {
                // helps help itslef
                $doc = $this->getDocs($this->server->route, $http_method, $filters);
            } else {
                // helps specified resource



                $doc = array($resource => $this->getDocs($this->server->route, $http_method, $filters));
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
    protected function getDocs(Entity $entity, array $filters=null)
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
