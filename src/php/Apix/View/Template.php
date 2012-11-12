<?php
namespace Apix\View;

use Apix\View\ViewModel;

abstract class Template
{

	/**
	 * Default Template class.
	 */
	public static $default_class = 'Apix\View\Template\Mustache';


	/**
	 * Renders the model view into the template.
	 *
	 * @param  ViewModel  $model
	 * @abstract
	 */
	abstract public function render(ViewModel $model);

}