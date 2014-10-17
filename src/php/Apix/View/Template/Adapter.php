<?php
namespace Apix\View\Template;

interface Adapter
{

    /**
     * Renders the model view into the template layout.
     *
     * @param ViewModel $view
     */
    public function render(ViewModel $view);

}
