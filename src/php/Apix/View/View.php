<?php
namespace Apix\View;

use Apix\View\ViewModel; 

class View
{

	/**
	 * @var	Template
	 */
	protected $template;

	/**
	 * @var	ViewModel
	 */
	protected $model;

	/**
	 * Factory method.
	 *
	 * @param   mixed  
	 * @param   mixed  
	 * @return  self
	 */
	public static function factory($template = null, $model = null)
	{
		return new self($template, $model);
	}

	/**
	 * Sets [Template] and [ViewModel]. If a string is passed as $template
	 * then it will be used a the path to the template and a Template
	 * instance will be created using [Template::$default_class].
	 *
	 * If an array is passed as $viewmodel then [ViewModel::$default_class]
	 * will be used to create a ViewModel instance and then the data will
	 * be set on that object.
	 *
	 * @param   mixed  
	 * @param   mixed  
	 * @return  void
	 */
	public function __construct($template = null, $model = null)
	{
		if ($template !== null) {
			if (is_string($template)) {
//				$this->set_filename($template);
			} else if ($template instanceof Template) {
				$this->template($template);
			}
		}

		if($model !== null) {
			if (is_array($model)) {

				// set the model view
				$this->setViewModelFromArray($model);

			} else if ($model instanceof ViewModel) {
				$this->model($model);
			}
		}
	}

	// todo
	public function setViewModelFromArray(array $model)
	{
		$key = key($model);
		ViewModel::$default_key = $key;
		
		$class = ViewModel::$default_class . '\\' . ucfirst($key);
		
		if(class_exists($class)) {
			ViewModel::$default_class = ViewModel::$default_class . '\\' . ucfirst($key);
		}

		$this->model()->set($model);
	}

	/**
	 * Set/Get Template. If getting and no template is set then
	 * we create an instance using [Template::$default_class].
	 *
	 * @param   Template  
	 * @return  $this
	 */
	public function template(Template $template = null)
	{
		if ($template === null) {
			if ($this->template === null) {
				$class = Template::$default_class;
				$this->template = new $class;
			}

			return $this->template;
		}

		$this->template = $template;
		return $this;
	}

	/**
	 * Get/Set [ViewModel]. If getting and no [ViewModel] set we then 
	 * create an instance using [ViewModel::$default_class].
	 *
	 * @param   ViewModel
	 * @return  ViewModel
	 */
	public function model(ViewModel $model = null)
	{
		if ($model === null) {
			if ($this->model === null) {
				$class = ViewModel::$default_class;
				$this->model = new $class;
			}

			return $this->model;
		}
		$this->model = $model;
		return $this;
	}

	/**
	 * Passes [ViewModel] to [Template::render()] and returns
	 * a final string.
	 *
	 * @param    mixed   Can be Template or string path to template
	 * @return   string
	 */
	public function render($template = null)
	{
		if ($template instanceOf Template) {
			$this->template($template);
		} 
		//else if (is_string($template))
		// {
		// 	$this->set_filename($template);
		// }
		//$this->model()->debug();
		return (string) $this->template()->render( $this->model() );
	}

	/**
	 * Magic method, render
	 *
	 * @return  string
	 */
	public function __toString()
	{
		return $this->render();
	}

}