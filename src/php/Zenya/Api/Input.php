<?php
namespace Zenya\Api;

use Zenya\Api\Listener,
    Zenya\Api\Request,
    Zenya\Api\Input\InputInterface,
    Zenya\Api\Input\Xml,
    Zenya\Api\Input\Json;

class Input
{
    /**
     * Get (& parse) body-data (refactor)
     *
     * @return array
     */
    static public function getBodyData(Request $request)
    {
        #$request = $request === null ? Request::getInstance() : $request;

        if ( $request->hasHeader('CONTENT_TYPE') && $request->hasBody() ) {
            $ct = $request->getHeader('CONTENT_TYPE');
            switch (true) {
                // application/x-www-form-urlencoded
                case (strstr($ct, '/x-www-form-urlencoded')):
                    $params = $request->getParams();
                break;

                // 'application/json'
                case (strstr($ct, '/json')):
                    $input = new Input\Json;
                    $params = $input->decode($request->getBody(), true);
                    #$this->request->setParams($r);
                break;

                // 'text/xml', 'application/xml'
                case (strstr($ct, '/xml')
                    && (!strstr($ct, 'html'))):
                    $input = new Input\Xml;
                    $params = $input->decode($request->getBody(), true);
                    #$this->request->setParams($r);
                break;

                default:
                    $params = null;
            }
            return $params;
        }
    }
}