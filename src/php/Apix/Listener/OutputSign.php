<?php
namespace Apix\Listener;

use Apix\Response;

class OutputSign extends AbstractListener
{

    public static $hook = array('response', 'early');

    protected $options = array(
        'enable'   => true,               // wether to enable or not
    );

    public function update(\SplSubject $response)
    {
        if ( false === $this->options['enable'] ) {
            return false;
        }

        $request = $response->getRequest();
        $route = $response->getRoute();
        $http_code = $response->getHttpCode();

        $signature = array(
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

        $response->results['signature'] = $signature;
    }

}