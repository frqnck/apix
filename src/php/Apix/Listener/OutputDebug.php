<?php
namespace Apix\Listener;

use Apix\Response;

class OutputDebug extends AbstractListener
{

    public static $hook = array('response', 'early');

    protected $options = array(
        'enable'     => true,               // wether to enable or not
        'timestamp'  => 'D, d M Y H:i:s T', // RFC 1123
    );

    public function update(\SplSubject $response)
    {
        if ( false === $this->options['enable'] ) {
            return false;
        }

        $request = $response->getRequest();
        $route = $response->getRoute();

        $debug = array(
            'timestamp'     => gmdate($this->options['timestamp']),
            'request'       => sprintf('%s %s%s',
                                    $request->getMethod(),
                                    $request->getRequestUri(),
                                    isset($_SERVER['SERVER_PROTOCOL'])
                                        ? ' ' . $_SERVER['SERVER_PROTOCOL']
                                        : null
                               ),
            'headers'       => $response->getHeaders(),
            'output_format' => $response->getFormat(),
            'router_params' => $route->getParams(),
            'plugins'       => 'todo',
            'memory'        => $this->convert(memory_get_usage())
                               . '~' . $this->convert(memory_get_peak_usage())

        );

        if(defined('APIX_START_TIME')) {
            $debug['time'] = round(microtime(true) - APIX_START_TIME, 3) . ' seconds';
        }

        // X-Auth-Key
        if(isset($_SERVER['X_AUTH_USER'])) {
            $debug['user'] = $_SERVER['X_AUTH_USER'];
        }

        static $i = 0;
        ++$i;
        $response->results['debug'.$i] = $debug;
    }

    public function convert($int)
    {
        $unit = array('B','kB','MB','GB','TB','PB');
        return round($int/pow(1024,($i=floor(log($int,1024)))), 2) . $unit[$i];
    }

}