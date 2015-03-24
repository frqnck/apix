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

class View
{

    /**
     * Holds a ViewModel object.
     * @var	ViewModel
     */
    protected $view_model;

    /**
     * Holds a Template object.
     * @var	Template
     */
    protected $template;

    /**
     * View constructor, sets the ViewModel.
     *
     * @see http://en.wikipedia.org/wiki/Model_View_ViewModel
     * @param ViewModel $view_model Optional. The view model to set.
     * @param array     $model_data Optional. An array of data to model.
     */
    public function __construct(ViewModel $view_model = null, array $model_data = null)
    {
        if (null !== $view_model) {
            $this->setViewModel($view_model);
        }

        if (null !== $model_data) {
            $this->view_model->set($model_data);
        }
    }

    /**
     * Sets the ViewModel object.
     *
     * @param  ViewModel $view_model The view model to set.
     * @return ViewModel Provides method chaining.
     */
    public function setViewModel(ViewModel $view_model)
    {
        return $this->view_model = $view_model;
    }

    /**
     * Returns the current ViewModel object.
     *
     * @return ViewModel
     */
    public function getViewModel()
    {
        return $this->view_model;
    }

    /**
     * Sets the Template instance.
     *
     * If a string is passed as $template
     * then it will be used a the path to the template and a Template
     * instance will be created using [Template::$default_class].
     *
     * @param Template|string|null $template A string or an instance of Template.
     */
    public function setTemplate($template, array $options = null)
    {
        if (!$template instanceof Template) {
            $t = new Template();
            $t->setEngine($template);
            $class = $t->getEngine();
            $template = new $class($options);
        }

        $this->template = $template;
    }

    /**
     * Renders the given layout or retrieved from the view model if null.
     *
     * @param  string|null Template layout to render.
     * @return string
     */
    public function render($layout = null)
    {
        $layout = is_string($layout)
                    ? $layout
                    : $this->view_model->getLayout();

        $this->template->setLayout( $layout );

        return $this->template->render( $this->view_model );
    }

    /**
     * Returns a string representaion of the view.
     * That magic method does not play nicely with exception so best avoided!
     *
     * @todo  depreciate this!!!
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}
