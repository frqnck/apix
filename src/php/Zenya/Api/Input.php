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
     * Returns the format from an HTTP context.
     *
     * @param  string   $context    The context to extract the format from.
     * @return string               The extracted format.
     */
    static public function getFormat($context)
    {
        switch (true) {
            // 'application/json'
            case (strstr($context, '/json')):
                $format = 'json';
                break;

            // 'text/xml', 'application/xml'
            case (strstr($context, '/xml')
                && (!strstr($context, 'html'))):
                $format = 'xml';
        }

        return isset($format) ? $format : null;
    }

    /**
     * Returns the format from an HTTP Accept.
     *
     * @param  Request $request
     * @return string  The output format
     */
    static public function getAcceptFormat(Request $request)
    {
        if ($request->hasHeader('HTTP_ACCEPT')) {
            $accept = $request->getHeader('HTTP_ACCEPT');

            if (!$format = self::getFormat($accept)) {
                // 'application/javascript'
                $format = strstr($accept, '/javascript') ? 'jsonp' : null;
            }
        }
        return isset($format) ? $format : false;
    }

    /**
     * Get & parse body-data.
     *
     * @param  Request  $request
     * @param  boolean  $assoc   Wether to convert objects to associative arrays.
     * @return array
     */
    static public function getBodyData(Request $request, $assoc=true)
    {
        if ($request->hasBody() && $request->hasHeader('CONTENT_TYPE')) {
            $ctx = $request->getHeader('CONTENT_TYPE');

            // application/x-www-form-urlencoded
            if(strstr($ctx, '/x-www-form-urlencoded')) {
                return $request->getParams();
            } else if ($format = self::getFormat($ctx) ) {
                $class = __NAMESPACE__ . '\Input\\' . ucfirst($format);
                $input = new $class;
                return $input->decode($request->getBody(), $assoc);
                #$this->request->setParams($r);
            }

            return null;
        }
    }

}