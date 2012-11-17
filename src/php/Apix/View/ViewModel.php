<?php
namespace Apix\View;

use Apix\View\ViewModel as Model;

class ViewModel
{

	/**
	 * Default View Model key.
	 */
	public static $default_key = null;

	/**
	 * Default View Model class.
	 */
	public static $default_class = 'Apix\View\ViewModel';

	/**
	 * Assigns a property.
	 *
	 *     // This value can be accessed as {{foo}} within the template
	 *     $view->set('foo', 'my value');
	 *
	 * You can also use an array to set several values at once:
	 *
	 *     // Create the values {{food}} and {{beverage}} in the template
	 *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
	 *
	 * @param   string|array  	variable name or an array of variables
	 * @param   mix				value
	 * @return  $this
	 */
	public function set($mix, $value = null)
	{
		if (is_array($mix)) {
			$mix = isset($mix[self::$default_key])
					? $mix[self::$default_key]
					: array();

			foreach ($mix as $name => $value) {
				$this->{$name} = $value;
			}
		} else {
			$this->{$mix} = $value;
		}
		return $this;
	}

    public function get($key)
    {
        $v = is_array($this->{$key}) ?  $this->{$key} : (array) $this->{$key};
        array_walk_recursive($v, function(&$v){$v=ViewModel::htmlizer($v);});
        return $v;
    }

    static public function htmlizer(&$v)
    {
        $pattern = array(
          '/((?:[\w\d]+\:\/\/)?(?:[\w\-\d]+\.)+[\w\-\d]+(?:\/[\w\-\d]+)*(?:\/|\.[\w\-\d]+)?(?:\?[\w\-\d]+\=[\w\-\d]+\&?)?(?:\#[\w\-\d]*)?)/', # URL
          '/([\w\-\d]+\@[\w\-\d]+\.[\w\-\d]+)/', # Email
          // '/\[([^\]]*)\]/', # Bold
          // '/\{([^}]*)\}/', # Italics
          // '/_([^_]*)_/', # Underline
          '/\s{2}/', # Linebreak
        );
        $replace = array(
          '<a href="$1">$1</a>',
          '<a href="mailto:$1">$1</a>',
          // '<b>$1</b>',
          // '<i>$1</i>',
          // '<u>$1</u>',
            '<br />'
        );
        return preg_replace($pattern, $replace, $v);
    }


	/**
	 * Assigns a value by reference. The benefit of binding is that values can
	 * be altered without re-setting them. It is also possible to bind variables
	 * before they have values. Assigned values will be available as a
	 * variable within the template file:
	 *
	 *     // This reference can be accessed as {{ref}} within the template
	 *     $view->bind('ref', $bar);
	 *
	 * @param   string   variable name
	 * @param   mixed    referenced variable
	 * @return  $this
	 */
	public function bind($key, & $value)
	{
		$this->{$key} =& $value;

		return $this;
	}

	/* generic helpers */

    public function hasMany($mix)
    {
        if (is_string($mix) && isset($this->{$mix})) {
            return count($this->{$mix})>1;
    	} else if(is_array($mix)) {
            return count($mix)>1;
        }
        return false;
    }

    public function getViewLayout()
    {
    	return $this->_layout;
    }

    public function debug($data=null)
    {
    	$data = null !== $data ? $data : $this;

        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

}