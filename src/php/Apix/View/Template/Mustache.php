<?php
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
        $dir = APP_SRC . '/data/templates/html';
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