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

use Apix\Response;

class OutputDebug extends PluginAbstract
{

    public static $hook = array('response', 'early');

    protected $options = array(
        'enable'     => true,               // whether to enable or not
        'name'       => 'debug',            // the header name
        'prepend'    => false,              // whether to prepend the debugging
        'timestamp'  => 'D, d M Y H:i:s T', // stamp format, default to RFC1123
        'extras'     => null,               // extras to inject, string or array
    );

    public function update(\SplSubject $response)
    {
        if (false === $this->options['enable']) {
            return false;
        }

        $request = $response->getRequest();
        $route = $response->getRoute();

        $headers = $response->getHeaders();

        if (isset($_SERVER['X_AUTH_USER'])) {
            $headers['X_AUTH_USER'] = $_SERVER['X_AUTH_USER'];
        }

        if (isset($_SERVER['X_AUTH_KEY'])) {
            $headers['X_AUTH_KEY'] = $_SERVER['X_AUTH_KEY'];
        }

        $data = array(
            'timestamp'     => gmdate($this->options['timestamp']),
            'request'       => sprintf('%s %s%s',
                                    $request->getMethod(),
                                    $request->getRequestedUri(),
                                    isset($_SERVER['SERVER_PROTOCOL'])
                                    ? ' ' . $_SERVER['SERVER_PROTOCOL'] : null
                               ),
            'headers'       => $headers,
            'output_format' => $response->getFormat(),
            'router_params' => $route->getParams(),
            'memory'        => self::formatBytesToString(memory_get_usage())
                               . '~' .  self::formatBytesToString(
                                            memory_get_peak_usage()
                                        )
        );

        if (defined('APIX_START_TIME')) {
            $data['timing'] = round(microtime(true) - APIX_START_TIME, 3) . ' seconds';
        }

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

    /**
     * Formats bytes into a human readable string
     *
     * @param  int     $bytes
     * @param  boolean $long
     * @return string
     */
    public static function formatBytesToString($bytes, $long=false)
    {
        $bytes = (int) $bytes;

        $unit = false == $long
                    ? array('B','kB','MB','GB','TB','PB','EB')
                    : array(
                        'bytes','kilobytes','megabytes','gigabytes',
                        'terabytes','petabytes','exabytes'
                    );

        $i = floor(log($bytes, 1024));

        return round($bytes/pow(1024, $i), 2) . ' ' . $unit[$i];
    }

}
