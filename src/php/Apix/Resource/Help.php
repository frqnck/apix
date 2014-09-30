<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix\Resource;

use Apix\Entity,
    Apix\Server,
    Apix\Request,
    Apix\Router,
    Apix\View\ViewModel;

/**
 * Help
 * This resource entity provides in-line referencial to all the API resources and methods.
*/
class Help
{
    // only use in verbose mode.
    public $private_nodeName = 'verbose';

    /**
     * Display the manual of a resource entity
     *
     * This resource entity provides in-line referencial to all the API resources and methods.
     * By specify a resource and method you can narrow down to specific section.
     *
     * @param string $path    A string of characters used to identify a resource.
     * @param array  $filters Filters can be use to narrow down the resultset.
     *
     * @example <pre>GET /help/path/to/entity</pre>
     * @id help
     * @usage The OPTIONS method represents a request for information about the
     * communication options available on the request/response chain
     * identified by the Request-URI. This method allows the client to determine
     * the options and/or requirements associated with a resource,
     * or the capabilities of a server, without implying a resource action or
     * initiating a resource retrieval.
     * @see <pre>http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2</pre>
     */
    public function onRead(Server $server, array $filters=null)
    {
        $this->route = $server->getRoute();

        $path = preg_replace('@^.*help(\.\w+)?@', '', $server->request->getUri());

        # $path = rawurldecode($path);

        // if (
        //     !empty($path)
        //     && $server->resources->has($path)
        // ) {
            $server->getRoute()->setName($path);
        // }
        return $this->onHelp($server, $filters);
    }

    /**
     * Outputs info for a resource entity.
     *
     * The OPTIONS method represents a request for information about the
     * communication options available on the request/response chain
     * identified by the Request-URI. This method allows the client to determine
     * the options and/or requirements associated with a resource,
     * or the capabilities of a server, without implying a resource action or
     * initiating a resource retrieval.
     *
     *
     * @param  Server $server  The main server object.
     * @param  array  $filters An array of filters.
     * @return array  The array documentation.
     *
     * @api_link    OPTIONS /path/to/entity
     * @api_link    OPTIONS /*
     * @private 1
     */
    public function onHelp(Server $server, array $filters=null)
    {
        $this->route = $server->getRoute();

        $entity = $this->route->getName() != '/' && $this->route->getName() != '/*'
            && $this->route->getPath() != '/help'
            ? $server->resources->get($this->route, false)
            : null;

        // TOC of all entities.
        if (null === $entity) {

            $docs = array();
            foreach ($server->resources->toArray() as $path => $entity) {
                if (!$entity->hasRedirect()) {
                    #$doc[$path] = $this->getDocs($entity, $filters);
                    $docs['items'][] = $this->getDocs(null, $path, $entity, $filters);
                }
            }

            // insures the top node is set to help.
            $this->route->setController('help');

            // // set Content-Type (negotiate or default)
            // if(
            //      $request->hasHeader('CONTENT_LENGTH')
            //      || $request->hasHeader('TRANSFER_ENCODING')
            // ) {
            //     // @expect   client request has an entity-body (indicated by Content-Length or Transfer-Encoding) then client's Content-Type must be set.
            //     return array('doc'=>'Todo: return all the resource doc as per CONTENT_LENGTH and/or TRANSFER_ENCODING');
            // }

        // Manual for the specified entity doc.
        } else {
            // return array($this->doc_nodeName => $this->getDocs($route->getName(), $entity, $filters));

            $docs = $this->getDocs(
                $server->request->getParam('method'), $this->route->getName(), $entity, $filters
            );
        }

        return $docs;
    }

    /**
     * Get an entity documentaion.
     *
     * @param  string          $method       The Request-method for that entity.
     * @param  string          $path         The Request-URI for that entity.
     * @param  EntityInterface $entity       An Entity object.
     * @param  array           $filters=null An array of filters.
     * @return array           The array documentation.
     */
    protected function getDocs($method, $path, Entity $entity, array $filters=null)
    {
        // $man = $this->getParam('resource');
        // $resource = Zenya_Api_Resource::getInternalAppelation($man);
        // $help = new Zenya_Api_ManualParser($resource, $man, 'api_');
        // $this->view = $help->toArray();

        $verbose = isset($_REQUEST['verbose']) ? $_REQUEST['verbose'] : false;

        if (null !== $method || $entity->hasMethod($method) ) {

            // get specified method.
            $docs = $entity->getDocs($method);

        } else {

            // get all docs.
            $docs = $entity->getDocs();
        }

        $docs['path'] = $path;

        if ($verbose) {
            $docs[$this->private_nodeName] = array(
                'TODO: verbose/admin/private mode (display AUTH/ACL, Cache entries, etc...)'
            );
        }

        return $docs;
    }

}
