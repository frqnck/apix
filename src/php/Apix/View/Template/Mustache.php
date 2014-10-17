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
    public function __construct(array $options = array())
    {
        $opts = array('extension' => '.ms');
        $defaultOptions = array(
            'loader'            => new \Mustache_Loader_FilesystemLoader($options['view_dir'], $opts),
            'partials_loader'   => new \Mustache_Loader_FilesystemLoader($options['view_dir'] . '/partials', $opts),
            'cache'             => '/tmp/cache/mustache'
        );

        $this->options = $defaultOptions + $options;
    }

    /**
     * {@inheritdoc}
     */
    public function render(ViewModel $model)
    {
        // $template = '<h1> {{title}} </h1> <ul> {{#sites}} <li> {{#url}} {{.}} {{/url}} </li> {{/sites}}  </ul>';
        $m = new \Mustache_Engine($this->options);

        return $m->render($this->layout, $model);
    }

}
