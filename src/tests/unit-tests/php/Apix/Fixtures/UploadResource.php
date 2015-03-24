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

namespace Apix\Fixtures;

use Apix\Server;
use Apix\Request as Request;
use Apix\Input as Input;

/**
 * Upload resource
 *
 * This is a test uplaoding resource. Use for testing and demo.
 *
 * @api_public true
 * @api_version 1.0
 * @api_permission admin
 * @api_randomName classRandomValue
 */
class UploadResource
{

    /**
     * constructor
     *
     * @return void
     */
   #public function __construct(Server $server)
   public function __construct()
    {
#        $this->server = $server;
    }

    /**
     * GET
     *
     * @param  string $type
     * @param  string $debug
     * @return array
     *
     * @api_role public
     *
     */
    public function onRead($type, $debug=false, Request $request=null)
    {
        return array('GET');
    }

    /**
     * POST
     *
     * @param  string $type
     * @return array
     *
     * @api_role public
     *
     */
    public function onCreate($type, $debug=false)
    {
        return array(
            'body'      => $this->server->request->getBody(),
            'params'    => $this->server->getBodyData()
        );
    }

}
