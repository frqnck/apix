<?php
namespace Apix\Plugin;

use Apix\Service,
    Apix\HttpRequest,
    Apix\Exception;

/**
 * Apix plugin providing Cross-Origin Resource Sharing
 *
 * @see http://www.w3.org/TR/cors/
 */
class Cors extends PluginAbstractEntity
{
    public static $hook = array('entity', 'early');

    protected $annotation = 'api_cors';

    protected $options = array(
        'enable'    => true,         // enable or not
        
        // -- whitelist
        'scheme'    => 'https?',     // allows http and https, 
        'host'      => '.*\.info\.com', #'foo.bar',    // the domain(s) or ip(s) allowed.
        'port'      => '(:[0-9]+)?', // port definition, here any number or none

        // -- CORS directives
        'allow-origin'      => 'origin', // 'origin', '*', or string-list or null
        'allow-credentials' => false,
        'expose-headers'    => null,

        // -- preflight
        'max-ages'      => 3600,
        'allow-methods' => 'GET, POST',
        'allow-headers' => 'some',
    );

    public function update(\SplSubject $entity)
    {
        // skip if null
        if (
            false === $this->options['enable']
            // || null === $entity->getAnnotationValue($this->annotation)
        ){
            return false;
        }

        $this->entity = $entity;
        
        $host = $this->getSubTagValues('host');

        // $this->options['enable'] = (bool) $values[0];
        // var_dump( $host );exit;

        $response = Service::get('response');
        $request = $response->getRequest();
        // $request = HttpRequest::GetInstance();  //Service::get('request');

        // Grab the Origin header.
        $http_origin = array_key_exists('HTTP_ORIGIN', $_SERVER)
                        ? $_SERVER['HTTP_ORIGIN']
                        : null;

        # If the Origin is whitelisted then it is some kind of CORS request.
        if (
            $http_origin
            // && $_SERVER['HTTP_HOST'] == $http_origin
            && $this->isOriginAllowed(
                $http_origin,
                $this->options['host'],
                $this->options['port'], $this->options['scheme']
            )
        ) {
            // 5.1 Access-Control-Allow-Origin
            $http_origin = $this->options['allow-origin'] == 'origin'
                            ? $http_origin
                            : $this->options['allow-origin'];
            $response->setHeader('Access-Control-Allow-Origin', $http_origin);

            // 5.2 Access-Control-Allow-Credentials
            // The actual request can include user credentials
            // (e.g. cookies, XmlHttpRequest.withCredentials=true)
            $response->setHeader('Access-Control-Allow-Credentials',
                                    (bool) $this->options['allow-credentials']);

            // 5.3 Access-Control-Expose-Headers
            // Which response headers are available (besides the generic ones)
            if($this->options['expose-headers']) {
                $response->setHeader('Access-Control-Expose-Headers',
                                            $this->options['expose-headers']);
            }

            // TODO: Preflight...
            $preflight = false;
            if($preflight) {
                // 5.4 Access-Control-Max-Age
                // Cache the preflight request for the provided amount of seconds.
                if($this->options['max-ages'] > 0) {
                    $response->setHeader('Access-Control-Max-Age',
                                            (int) $this->options['max-ages']);
                }

                // 5.5 Access-Control-Allow-Methods
                $response->setHeader('Access-Control-Allow-Methods',
                                            $this->options['allow-methods']);

                // 5.6 Access-Control-Allow-Headers
                $response->setHeader('Access-Control-Allow-Headers',
                                            $this->options['allow-headers']);
            }

        } else {
            throw new Exception('Not a valid CORS request.', 400);
        }
    }

    /**
     * Checks the provided origin as a CORS requests.
     *
     * @param string $host      The host domain(s) or IP(s) to match.
     * @param string $port      Default to any port or none provided.
     * @param string $scheme    Default tp 'http' and 'https'.
     */
    public function isOriginAllowed($origin, $host, $port='(:[0-9]+)?', $scheme='https?')
    {
        $regex = '`^' . $scheme . ':\/\/' . $host . $port . '$`';
        return (bool) preg_match($regex, $origin);
    }

}