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
 * This resource entity provides in-line referencial of all the API resources.
 */
class Help
{
    // only use in verbose mode.
    public $private_nodeName = 'verbose';

    private $rel_path = '/help';

    /**
     * Help - Provides in-line referencial to this API.
     *
     * This resource entity provides documentation and in-line referencial to this API's resources.
     * By specify a resource and method you can narrow down to specific section.
     *
     * @param  string $resource Optional. A string of characters used to identify
     *                                    a specific resource entity.
     *                                    e.g. /help/resource
     * @param  array  $filters  Optional. Some filters to narrow down the resultset.
     * @global string $method    Default "GET". The resource HTTP method to interact.
     * @return array  An array documentating either all the available resources
     *                or if provided, the specified <b>:resource</b>.
     *
     * @example <p><pre>apixs:
     *    help:
     *       title: Help - Provides in-line referencial to this API.
     *       description: This resource entity provides documentation and ...
     * </pre></p>
     * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
     */
    public function onRead($resource=null, array $filters=null)
    {
        $server = \Apix\Service::get('server');
        $resource = preg_replace(
            '@^(.*?)' . preg_quote($this->rel_path) . '(\.\w+)?@',
            '',
            $server->request->getUri()
        );

        // $resource = rawurldecode($resource);
        // if (!$server->resources->has($resource)) {
        //     return array();
        // }

        // if (
        //     !empty($resource)
        //     && $server->resources->has($resource)
        // ) {
            $server->getRoute()->setName($resource);
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
     * @return array  An array of documentation data.
     *
     * @api_link    OPTIONS /path/to/entity
     * @api_link    OPTIONS /*
     * @apix_man_toc_hidden
     */
    public function onHelp(Server $server, array $filters=null)
    {
        $route = $server->getRoute();

        // insures the top node is set to help.
        $route->setController('help');

        $entity = $route->getName() != '/' && $route->getName() != '/*'
            && $route->getPath() != '/help'
            ? $server->resources->get($route, false)
            : null;

        // TOC of all entities.
        if (null === $entity) {

            $docs = array(
                'items' => self::getResourcesDocs($server)
            );

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
            $docs = self::getEntityDocs(
                $entity,
                $server->request->getParam('method') ?: 'GET',
                $route->getName()
            );
        }

        return $docs;
    }

    /**
     * Get the documentation of all the resource entities.
     *
     * @param  Server $server
     * @return array  The documentation for all resource entities.
     */
    public static function getResourcesDocs(Server $server)
    {
        $docs = array();
        $redir = array();
        $resources = $server->resources->toArray();
        ksort($resources);
        foreach ($resources as $path => $entity) {
            if (!$entity->hasRedirect()) {
                $item = self::getEntityDocs($entity, null, $path);
                $item['path'] = isset($redir[$path])
                                    ? $redir[$path]
                                    : $path;
                $docs[] = $item;
            } else {
                $redir[$entity->getRedirect()] = $path;
            }
        }

        return $docs;
    }

    /**
     * Get the documentation for the provided entity and method.
     *
     * @param  EntityInterface $entity The Entity object to interact with.
     * @param  string|null     $method Optional. The Request-method or all.
     * @param  string|null     $path   Optional. Request-URI for that entity.
     * @return array           The entity array documentation.
     */
    public static function getEntityDocs(Entity $entity, $method=null, $path=null)
    {
        $docs = $entity->getDocs($method);
        // $docs = (null !== $method || $entity->hasMethod($method))
        //     ? $entity->getDocs($method) // get the specified doc method.
        //     : $entity->getDocs();       // get all the docs for all methods

        $docs['path'] = $path;

        return $docs;
    }

}
