<?php

namespace Zenya\Api\Fixtures;

use Zenya\Api\Server;
use Zenya\Api\Request as Request;
use Zenya\Api\Input as Input;

/**
 * BlankResource
 *
 * This is just a blank resource. Use for testing and demo.
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
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Post
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
     * Post
     *
     * @param  string $type
     * @return array
     *
     * @api_role public
     *
     */
    public function onCreate($type, $debug=false)
    {
        #$request = $request === null ? Request::getInstance() : $request;

        return array(
            'body'      => $this->server->request->getBody(),
            'params'    => $this->server->getBodyData()
        );
    }

    public function OffonCreate($type, $debug=false, Request $request=null)
    {
        $request = $request === null ? Request::getInstance() : $request;

        if ($debug==true) {
            $results['debug'] = $request->getHeaders();
        }

        if ( $request->hasHeader('CONTENT_TYPE') && $request->hasBody() ) {

            $ct = $request->getHeader('CONTENT_TYPE');

            switch (true) {

                // application/x-www-form-urlencoded
                case (strstr($ct, '/x-www-form-urlencoded')):
                break;

                // 'application/json'
                case (strstr($ct, '/json')):
                    $input = new Input\Json;
                    $r = $input->decode($request->getBody(), true);
                    $request->setParams($r);
                break;

                // 'text/xml', 'application/xml'
                case (strstr($ct, '/xml')
                    && (!strstr($ct, 'html'))):
                    $input = new Input\Xml;
                    $r = $input->decode($request->getBody(), true);
                    $request->setParams($r);
            }

        }

        return array(
            'ct' => $ct,
            'body'      => $request->getBody(),
            'params'    => $request->getParams()
        );
    }

}
