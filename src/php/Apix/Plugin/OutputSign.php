<?php
namespace Apix\Plugin;

use Apix\Response;

class OutputSign extends PluginAbstract
{

    public static $hook = array('response', 'early');

    protected $options = array(
        'enable'    => true,        // wether to enable or not
        'name'      => 'signature', // the header name
        'prepend'   => false,       // wether to prepend the signature
        'extras'    => null,        // extras to inject, string or array
    );

    public function update(\SplSubject $response)
    {
        if (false === $this->options['enable']) {
            return false;
        }

        $request = $response->getRequest();
        $route = $response->getRoute();
        $http_code = $response->getHttpCode();

        $data = array(
            'resource'  => sprintf(
                            '%s %s',
                            $route->getMethod(),
                            $route->getName()
                        ),
            'status'    => sprintf(
                            '%d %s - %s',
                            $http_code,
                            Response::getStatusPrases($http_code),
                            Response::getStatusAdjective($http_code)
                        ),
            'client_ip' => $request->getIp(true)
        );

        if (null !== $this->options['extras']) {
            $data['extras'] = $this->options['extras'];
        }

        $name = $this->options['name'];
        if (true === $this->options['prepend']) {
            $response->results = array($name=>$data)+$response->results;
        } else {
            $response->results[$name] = $data;
        }
    }

}
