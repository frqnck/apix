<?php
namespace Apix\View;

use Apix\View\ViewModel;

class View
{

	/**
	 * @var	ViewModel
	 */
	protected $model;

	/**
	 * @var	Template
	 */
	protected $template;

	/**
	 * Constructor.
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
	public function __construct($model = null, $template = null)
	{
		// sort out the view model.
		if($model !== null) {
			if (is_array($model)) {

				// set the model view
				$this->setViewModelFromArray($model);

			} else if ($model instanceof ViewModel) {
				$this->model($model);
			}
		}

		// deal with the template engine.
		if ($template !== null) {
			if (is_string($template)) {
				$this->template()->setLayout($template);
			} else if ($template instanceof Template) {
				$this->template($template);
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
			ViewModel::$default_class = $class;
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
				$this->template = Template::getEngine();
			}

			return $this->template;
		}

		$this->template = $template;
		#return $this;
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
	public function render($layout = null)
	{
		if ($layout instanceOf Template) {
			$this->template($layout);
		} else if (is_string($layout)) {
			$this->template()->setLayout($layout);
		}
		#$this->model()->debug();

		return $this->template()->render( $this->model() );
	}


	/**
	 * Returns a string representaion of the view.
	 * That magic method does not play nicely with exception so best avoided!
	 *
	 * @todo  depreciate this!!!
	 * @return  string
	 */
	public function __toString()
	{
		return $this->render();
	}

}