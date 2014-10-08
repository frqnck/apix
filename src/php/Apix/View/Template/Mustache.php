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

namespace Apix\View\Template;

use Apix\View\Template,
    Apix\View\ViewModel;

class Mustache extends Template
{

    public $options = null;

    /**
     * Constructor.
     */
    public function __construct(array $options=array())
    {
        $distrib_path = \Apix\Service::get('config')['distrib_path'];
        
        $dir = $distrib_path . '/../templates/html';
        $opts = array('extension' => '.ms');
        $this->defaultOptions = array(
            'extension'         => '.ms',
            'loader'            => new \Mustache_Loader_FilesystemLoader($dir, $opts),
            'partials_loader'   => new \Mustache_Loader_FilesystemLoader($dir . '/partials', $opts),
            'cache'             => '/tmp/cache/mustache'
        );

        $this->options = $this->defaultOptions + $options;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ViewModel $model)
    {
        #$template = '<h1> {{title}} </h1> <ul> {{#sites}} <li> {{#url}} {{.}} {{/url}} </li> {{/sites}}  </ul>';
        $m = new \Mustache_Engine($this->options);

        return $m->render($this->layout, $model);
    }

}
