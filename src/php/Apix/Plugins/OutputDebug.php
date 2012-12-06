<?php
namespace Apix\Plugins;

use Apix\Response;

class OutputDebug extends PluginAbstract
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

        $headers = $response->getHeaders();

        if(isset($_SERVER['X_AUTH_USER'])) {
            $headers['X_AUTH_USER'] = $_SERVER['X_AUTH_USER'];
        }

        if(isset($_SERVER['X_AUTH_KEY'])) {
            $headers['X_AUTH_KEY'] = $_SERVER['X_AUTH_KEY'];
        }

        $debug = array(
            'timestamp'     => gmdate($this->options['timestamp']),
            'request'       => sprintf('%s %s%s',
                                    $request->getMethod(),
                                    $request->getRequestUri(),
                                    isset($_SERVER['SERVER_PROTOCOL'])
                                    ? ' ' . $_SERVER['SERVER_PROTOCOL'] : null
                               ),
            'headers'       => $headers,
            'output_format' => $response->getFormat(),
            'router_params' => $route->getParams(),
            'plugins'       => 'todo',
            'memory'        => $this->convert(memory_get_usage())
                               . '~' . $this->convert(memory_get_peak_usage())

        );

        if(defined('APIX_START_TIME')) {
            $debug['timing'] = round(microtime(true) - APIX_START_TIME, 3) . ' seconds';
        }

        $response->results['debug'] = $debug;
    }

    public function convert($int)
    {
        $unit = array('B','kB','MB','GB','TB','PB');
        return round($int/pow(1024,($i=floor(log($int,1024)))), 2) . $unit[$i];
    }

}