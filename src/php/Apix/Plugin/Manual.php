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

class Manual extends PluginAbstract
{

    public static $hook = array(
        'response',
        'early',
        'interface' => 'Apix\View\Template\Adapter'
    );

    protected $options = array(
        'enable'    => true, // wether to enable or not
        'templater' => 'Apix\View\Template\Mustache', // the template adapter
        'view_dir'  => null
    );

    /**
     * Constructor.
     *
     * @param array $options Array of options.
     */
    public function __construct(array $options=null)
    {
        if(!isset($options['view_dir'])) {
            $distrib_path = \Apix\Service::get('config')['distrib_path'];
            $options['view_dir'] = $distrib_path . '/../templates/html';
        }
        $this->setOptions($options);
    }

    public function update(\SplSubject $response)
    {
        if (
            false === $this->options['enable']
            || 'html' !== $response->getFormat()
            || 'help' != key($response->results)
        ) {
            return false;
        }

        $view = new View($response->results);
        $options = array('view_dir' => $this->options['view_dir']);
        $view->setTemplate($this->options['templater'], $options);
        $response->setOutput(
            $view->render()
        );
    }

}
