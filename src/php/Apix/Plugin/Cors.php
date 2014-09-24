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

namespace Apix\Plugin;

use Apix\Service,
    Apix\HttpRequest,
    Apix\Exception;

/**
 * Apix plugin providing Cross-Origin Resource Sharing
 *
 * @see http://www.w3.org/TR/cors/
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
 */
class Cors extends PluginAbstractEntity
{
    public static $hook = array('entity', 'early');

    protected $annotation = 'api_cors';

    protected $options = array(
        'enable'    => true,            // whether to enable or not

        // -- whitelist (regex)
        'scheme'    => 'https?',        // allows both http and https
        'host'      => '.*\.info\.com', // the allowed host domain(s) or ip(s)
        'port'      => '(:[0-9]+)?',    // the alowed port(s)

        // -- CORS directives
        'allow-origin'      => 'origin', // 'origin', '*', 'null', string-list
        'allow-credentials' => false,    // wether to allow Cookies and HTTP Auth
        'expose-headers'    => null,     // comma-delimited HTTP headers exposed

        // -- preflight
        'max-age'       => 3600,         // TTL in seconds for preflight
        'allow-methods' => 'GET,POST',   // comma-delimited HTTP methods allowed 
        'allow-headers' => 'x-apix',     // comma-delimited HTTP headers allowed
    );

    /**
     * @{@inheritdoc}
     */
    public function update(\SplSubject $entity)
    {
        $this->setEntity($entity);

        // skip this plugin if it is disable.
        if ( !$this->getSubTagBool('enable', $this->options['enable']) ) {
            return false;
        }

        if ( $host = $this->getSubTagString('host', $this->options['host']) ) {
            $this->options['host'] = $host;
        }

        // Grab the Origin: header.
        $http_origin = array_key_exists('HTTP_ORIGIN', $_SERVER)
                        ? $_SERVER['HTTP_ORIGIN']
                        : null;

        // If whitelisted then it is a valid CORS request.
        if (
            $http_origin
            // && $_SERVER['HTTP_HOST'] == $http_origin
            && self::isOriginAllowed(
                $http_origin,
                $this->options['host'],
                $this->options['port'], $this->options['scheme']
            )
        ) {
            $response = Service::get('response');

            // 5.1 Access-Control-Allow-Origin
            $http_origin = $this->options['allow-origin'] == 'origin'
                            ? $http_origin
                            : $this->options['allow-origin'];
            $response->setHeader('Access-Control-Allow-Origin', $http_origin);

            // 5.2 Access-Control-Allow-Credentials
            // The actual request can include user credentials
            // e.g. cookies, XmlHttpRequest.withCredentials=true
            if ($this->options['allow-credentials'] === true) {
                $response->setHeader('Access-Control-Allow-Credentials', true);
            }

            // 5.3 Access-Control-Expose-Headers
            // Which response headers are available (besides the generic ones)
            if ($this->options['expose-headers']) {
                $response->setHeader('Access-Control-Expose-Headers',
                                            $this->options['expose-headers']);
            }

            $request = $response->getRequest();
            if ( self::isPreflight($request) ) {
                
                // 5.4 Access-Control-Max-Age
                if ($this->options['max-age'] > 0) {
                    // Cache the request for the provided amount of seconds
                    $response->setHeader('Access-Control-Max-Age',
                                            (int) $this->options['max-age']);
                }

                // 5.5 Access-Control-Allow-Methods
                if ($request->hasHeader('Access-Control-Request-Method') ) {
                   if (!in_array(
                        $request->getHeader('Access-Control-Request-Method'), 
                        self::split($this->options['allow-methods'])
                    )) {
                        return self::exception(); 
                    }
                    $response->setHeader(
                        'Access-Control-Allow-Methods',
                        $this->options['allow-methods']
                    );
                }

                // 5.6 Access-Control-Allow-Headers
                if ($request->hasHeader('Access-Control-Request-Headers')) {
                    $req_headers = self::split(
                        $request->getHeader('Access-Control-Request-Headers')
                    );
                    $allowed = self::split($this->options['allow-headers']);
                    foreach($req_headers as $req_header) {
                        if (!in_array($req_header, $allowed)) {
                            return self::exception(); 
                        }
                    }
                    $response->setHeader(
                        'Access-Control-Allow-Headers',
                        $this->options['allow-headers']
                    );
                }
            }

            return true;
        }

        // so it must be an invalid CORS request 
        return self::exception();
    }

    /**
     * Throws a \DomainException.
     *
     * @throw \DomainException
     */
    public static function exception()
    {
        throw new \DomainException('Not a valid CORS request.', 403);
    }

    /**
     * Split and trim the provided string.
     *
     * @param  string  $str
     * @return array
     */
    public static function split($str)
    {
        return array_map('trim', explode(',', $str)); 
    }

    /**
     * Checks the provided origin as a CORS requests.
     *
     * @param  string  $host   The host domain(s) or IP(s) to match.
     * @param  string  $port   Default to any port or none provided.
     * @param  string  $scheme Default tp 'http' and 'https'.
     * @return boolean
     */
    public static function isOriginAllowed(
        $origin, $host, $port='(:[0-9]+)?', $scheme='https?'
    ) {
        $regex = '`^' . $scheme . ':\/\/' . $host . $port . '$`';

        return (bool) preg_match($regex, $origin);
    }


    /**
     * Checks for a preflighted request.
     *
     * @return boolean
     */
    public static function isPreflight(HttpRequest $request)
    {
        $method = $request->getMethod();
        return 
            // uses methods other than...
            !in_array($method, array('GET', 'HEAD', 'POST'))

            // if POST is used with a Content-Type other than...
            or ( $method == 'POST'
                 and !in_array(
                        $request->getHeader('CONTENT_TYPE'),
                        array(
                            'text/plain',
                            'multipart/form-data',
                            'application/x-www-form-urlencoded'
                        )
                    )
                )
            
            // if it is set with some custom request headers
            or $request->hasHeader('Access-Control-Request-Headers'); 
    }

}
