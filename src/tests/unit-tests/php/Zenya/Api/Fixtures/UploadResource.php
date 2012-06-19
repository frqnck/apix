<?php

namespace Zenya\Api\Fixtures;

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
    public function __construct()
    {
    }

    /**
     * Post
     *
     * @param  string $type
     * @return array
     *
     * @api_role admin
     *
     */
    public function onCreate($type, $debug=false, Request $request=null)
    {
        $request = $request === null ? Request::getInstance() : $request;
        
        $results = array(
            'body'          => $request->getBody(),
            'params'        => $request->getParams()
        );

        if($debug==true) {
            $results['debug'] = $request->getHeaders();
        }

        if ( $request->hasHeader('CONTENT_TYPE') ) {
            
            $ct = $request->getHeader('CONTENT_TYPE');
            $results['ct'] = $ct;

            switch (true) {
                
                // application/x-www-form-urlencoded
                case (strstr($ct, '/x-www-form-urlencoded')):
                break;

                // 'application/json'
                case (strstr($ct, '/json')):
                    $input = new Input\Json;
                    $results['paramsBody'] = $input->decode($request->getBody());
                break;

                // 'text/xml', 'application/xml'
                case (strstr($ct, '/xml')
                    && (!strstr($ct, 'html'))):
                    $input = new Input\Xml;
                    $results['paramsBody'] = $input->decode($request->getBody());
            }

        }

        return $results;
    }

}