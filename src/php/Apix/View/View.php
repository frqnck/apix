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
    protected $model;

    /**
     * Holds a Template object.
     * @var	Template
     */
    protected $template;

    /**
     * Constructor, sets the ViewModel ad Template objects. 
     *
     * If an array is passed as $viewmodel then ViewModel::$default_class
     * will be used to create a ViewModel instance and then the data will
     * be set on that object.
     *
     * @param   mixed|null  $model      An array or an instance of ViewModel.
     */
    public function __construct($model = null)
    {
        if (null !== $model) {
            if (is_array($model)) {
                // set the model view
                $this->setViewModelFromArray($model);
            } elseif ($model instanceof ViewModel) {
                $this->model($model);
            }
        }
    }

    /**
     * Sets the ViewModel from an array
     *
     * @param   array  $model      An array of data.
     */
    public function setViewModelFromArray(array $model)
    {
        $key = key($model);
        ViewModel::$default_key = $key;

        $class = ViewModel::$default_class . '\\' . ucfirst($key);
        if (class_exists($class)) {
            ViewModel::$default_class = $class;
        }

        $this->model()->set($model);
    }

    /**
     * Get/Set [ViewModel]. If getting and no [ViewModel] set we then
     * create an instance using [ViewModel::$default_class].
     *
     * @param   ViewModel
     * @return ViewModel
     */
    public function model(ViewModel $model = null)
    {

        if ($model === null) {
            if ($this->model === null) {
                $class = ViewModel::$default_class;
                $this->model = new $class();
            }

            return $this->model;
        }
        $this->model = $model;

        return $this;
    }

    /**
     * Sets the Template instance.
     *
     * If a string is passed as $template
     * then it will be used a the path to the template and a Template
     * instance will be created using [Template::$default_class].
     *
     * @param   Template|string|null  $template   A string or an instance of Template.
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
        $layout = is_string($layout) ? $layout : $this->model()->getViewLayout();
        $this->template->setLayout( $layout );

        return $this->template->render( $this->model() );
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
