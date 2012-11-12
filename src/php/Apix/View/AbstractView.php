<?php
namespace Apix\View;

use Apix\View\ViewModel as ViewModel; 

class AbstractViewOff extends \ArrayObject
{
    protected $view_model = null;

    /**
     * Sets the view model (ViewModel).
     *
     * @param   Model   $model
     */
    public function setViewModel(ViewModel $model)
    {
        $this->view_model = $model;
    }

    public function getViewModel()
    {
        return $this->view_model;
    }

}