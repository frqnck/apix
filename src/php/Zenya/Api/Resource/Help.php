<?php

namespace Zenya\Api\Resource;

use Zenya\Api\Entity,
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
        $this->verbose = isset($_REQUEST['verbose']) ? $_REQUEST['verbose'] : false;
    }

    public function getEntityFromPath($server)
    {
        $path = preg_replace('@^.*help(\.\w+)?@i', '', $server->request->getUri());
        if(!empty($path) && $server->resources->has($path)) {
            $server->route->setName($path);
            return $server->resources->get($server->route);
        }
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
    public function onRead(Server $server, array $filters=null)
    {
        echo 'GET HELP';
        $entity = $this->getEntityFromPath($server);
        $name = $server->getRoute()->getName();
        //$name = empty($name) ? 'all' : $name;
        return $this->onHelp($server, $entity, $filters);
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
            return $doc;

            // // set Content-Type (negotiate or default)
            // if(
            //      $request->hasHeader('CONTENT_LENGTH')
            //      || $request->hasHeader('TRANSFER_ENCODING')
            // ) {
            //     // @expect   client request has an entity-body (indicated by Content-Length or Transfer-Encoding) then client's Content-Type must be set.
            //     return array('doc'=>'Todo: return all the resource doc as per CONTENT_LENGTH and/or TRANSFER_ENCODING');
            // }

        } else {
            // output docs for the specified resource entity
            return $this->getDocs($entity, $filters);
        }
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
                'operator-manual' => 'TODO verbose/admin mode (display AUTH/ACL, Cache entries, etc...)',
                'end-user-manual' => $entity->getDocs()
            );
        }

        return $entity->getDocs();
    }

}