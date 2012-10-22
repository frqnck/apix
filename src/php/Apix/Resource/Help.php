<?php

namespace Apix\Resource;

use Apix\Entity,
    Apix\Server,
    Apix\Request,
    Apix\Router;

/**
 * Help resource
 *
 * The Help resource provides in-line referencial to the API resources and methods.
 * By specify a resource and method you can narrow down to specific section.
 * @cacheable true
 */
class Help
{
    public $doc_nodeName = 'documentation';

    // only use in verbose mode.
    public $private_nodeName = 'verbose';

    /**
     * Outputs help info for a resource path.
     *
     * Filters can be use to narrow down to a specified method.
     *
     * @param  Server $server  The main server object.
     * @param  array  $filters An array of filters.
     * @return array
     * @see     self::onHelp
     *
     * @api_link    GET /help/path/to/entity
     */
    public function onRead(Server $server, array $filters=null)
    {
        $path = preg_replace('@^.*help(\.\w+)?@i', '', $server->request->getUri());
        if (!empty($path) && $server->resources->has($path)) {
            $server->getRoute()->setName($path);
        }

        return $this->onHelp($server, $filters);
    }

    /**
     * Outputs help info for a resource entity.
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
     * @param  Server $server  The main server object.
     * @param  array  $filters An array of filters.
     * @return array  The array documentation.
     *
     * @api_link    OPTIONS /path/to/entity
     * @api_link    OPTIONS /*
     */
    public function onHelp(Server $server, array $filters=null)
    {
        $route = $server->getRoute();

        $entity = $route->getName() != '/' && $route->getName() != '/*'
            ? $server->resources->get($route, false)
            : null;

        // returns the whole api doc.
        if (null === $entity) {
            $doc = array();
            foreach ($server->resources->toArray() as $path => $entity) {
                if (!$entity->hasRedirect()) {
                    #$doc[$path] = $this->getDocs($entity, $filters);
                    $doc[] = $this->getDocs($path, $entity, $filters);
                }
            }

            // insures the top node is set to help.
            $route->setController('help');

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
            // returns the specified entity doc.
            return array($this->doc_nodeName => $this->getDocs($route->getName(), $entity, $filters));
        }

    }

    /**
     * Get an entity documentaion.
     *
     * @param  string          $path         The Request-URI for that entity.
     * @param  EntityInterface $entity       An Entity object.
     * @param  array           $filters=null An array of filters.
     * @return array           The array documentation.
     */
    protected function getDocs($path, Entity $entity, array $filters=null)
    {
        // $man = $this->getParam('resource');
        // $resource = Zenya_Api_Resource::getInternalAppelation($man);
        // $help = new Zenya_Api_ManualParser($resource, $man, 'api_');
        // $this->_output = $help->toArray();

        $verbose = isset($_REQUEST['verbose']) ? $_REQUEST['verbose'] : false;

        $out = $entity->getDocs();

        // unshift associatively (php sucks!)
        $out = array_reverse($out, true);
        $out['path'] =  $path;
        $out = array_reverse($out, true);

        if ($verbose) {
            $out[$this->private_nodeName] = 'TODO: verbose/admin/private mode (display AUTH/ACL, Cache entries, etc...)';
        }

        return $out;
    }

}
