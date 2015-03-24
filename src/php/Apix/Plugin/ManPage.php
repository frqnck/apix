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

use Apix\View\View;
use Apix\Service;

/**
 * Adds a human-friendly API Manual.
 *
 * @author Franck Cassedanne <franck at ouarz.net>
 */
class ManPage extends PluginAbstract
{

    public static $hook = array(
        'response',
        'early',
        'interface' => 'Apix\View\Template\Adapter'
    );

    protected $options = array(
        'enable'    => true,    // wether to enable or not

        // Append the given string to the named group annotation. 
        'example'   => null,
        'see'       => null,
        'link'      => null,
        'copyright' => 'Powered by APIx-server, copyright (C) 2010 Franck Cassedanne.',
        'license'   => null,
        
        'view_dir'  => null,    // to set the view dir.
        'rel_path'  => '/help', // the relative path to help (no version prefix)
        'templater' => 'Apix\View\Template\Mustache', // the template adapter

        // Anything below is automatically populated (extracted).
        'version'  => 'v1', // the version string (default value).
        'url_api'  => null, // the API absolute URL.
        'url_help' => null, // the Manual absolute URL (url_api+rel_path).
    );

    /**
     * Constructor.
     *
     * @param array $options Array of options.
     */
    public function __construct(array $options = null)
    {
        if ( !isset($options['enable']) || $options['enable']) {

            if (!isset($options['url_api'])) {

                // review thsi
                $uri = isset($_SERVER['SCRIPT_URI'])
                        ? $_SERVER['SCRIPT_URI']
                        : $_SERVER['REQUEST_URI'];

                preg_match(
                    '@^(.*(/?v[0-9]+))'
                    . preg_quote($this->options['rel_path'])
                    . '(.+)?$@i',
                    $uri,
                    $m
                );

                if(isset($m[1]) && !empty($m[1])) $options['url_api'] =  $m[1];
                if(isset($m[2]) && !empty($m[2])) $options['version'] =  $m[2];
            }
        }
        $this->setOptions($options);
        $this->options['url_help'] = $this->options['url_api']
                                     . $this->options['rel_path'];
    }

    public function update(\SplSubject $response)
    {
        if (
            false === $this->options['enable']
            || 'html' !== $response->getFormat()
        ) {
            return false;
        }

        $type = key($response->results);

        if ($type == 'error') {
            $response->results[$type]['items'] = array();
        }

        $view_model = '\Apix\View\ViewModel\\' . ucfirst($type);
        $view = new View(new $view_model(), $response->results[$type]);

        $config = Service::get('config');
        $view->getViewModel()->set('config', $config);
        if (null === $this->options['view_dir']) {
            $distrib_path = $config['distrib_path'];
            $this->options['view_dir'] = $distrib_path . '/../templates/html';
        }
        $view->getViewModel()->set('options', $this->options);

        $view->setTemplate(
            $this->options['templater'],
            array('view_dir' => $this->options['view_dir'])
        );

        $response->setOutput(
            $view->render()
        );
    }

    /**
     * TODO
     * $uri = $_SERVER['SCRIPT_URI']
     * $rel_path = $this->options['rel_path']
     */
    public static function getUrlApiAndVersion($uri, $rel_path)
    {
        preg_match(
            '@^(.*(/?v[0-9]+))' . preg_quote($rel_path) . '@i',
            $uri,
            $m
        );

        return $m;
    }

}
