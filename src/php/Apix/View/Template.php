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

namespace Apix\View;

use Apix\View\Template as Template;

 class Template #implements Template\Adapter
{

    /**
     * Name of the templating engine.
     * @var  string
     */
    protected $engine = 'Apix\View\Template\Mustache';

    /**
     * The name of the template layout.
     * @var string
     */
    protected $layout = 'default';

    /**
     * The path to the view directory.
     * @var string
     */
    protected $view_dir = null;

    /**
     * Sets the engine name.
     *
     * @param string|null $engine
     */
    public function setEngine($engine = null)
    {
        $engine = null !== $engine ? $engine : $this->engine;
        if (!class_exists($engine)) {
            throw new \RuntimeException(
                sprintf('Template class "%s" does not exist.', $engine)
            );
        }
        $this->engine = $engine;
    }

    /**
     * Returns the engine name.
     *
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Sets the name of the template layout.
     *
     * @param string $layout
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Sets the main directory for this template view.
     * @var string
     */
    public function setViewDir($dir)
    {
        $this->view_dir = $dir;
    }

}
