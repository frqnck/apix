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

/**
 * Model View ViewModel (MVVM)
 * @see  http://en.wikipedia.org/wiki/Model_View_ViewModel
 */
abstract class ViewModel
{

    /**
     * Option variable exposed to the templates.
     * @var array
     */
    public $options = array();

    /**
     * Config variable exposed to the templates.
     * @var array|null
     */
    public $config = null;

    /**
     * Deals with the request params/filters definitions.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

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
     * @param  string|array $blob  A string key or an associative array to set.
     * @param  mixed        $value The value to set if the blob is a string.
     * @return $this
     */
    public function set($blob, $value = null)
    {
        if (is_array($blob)) {
            foreach ($blob as $key => $value) {
                $this->{$key} = $value;
            }
        } else {
            $this->{$blob} = $value;
        }

        return $this;
    }

    public function get($key, $htmlize=true)
    {
        $v = isset($this->{$key}) ? (array) $this->{$key} : array();

        if (isset($this->options[$key])) {
            array_push($v,
                // is_array($this->options[$key])
                // ? $this->options[$key][0]
                // :
                 $this->options[$key]
            );
    }

        if ($htmlize) {
            array_walk_recursive($v, function (&$v) {
                $v = ViewModel::htmlizer($v);
            });
        }

        return $v;
    }

    public static function htmlizer($string)
    {
        $pattern = array(
          '/((?:[\w\d]+\:\/\/)?(?:[\w\-\d]+\.)+[\w\-\d]+(?:\/[\w\-\d]+)*(?:\/|\.[\w\-\d]+)?(?:\?[\w\-\d]+\=[\w\-\d]+\&?)?(?:\#[\w\-\d\.]*)?)/', # URL
          '/([\w\-\d]+\@[\w\-\d]+\.[\w\-\d]+)/', # email
          // '/\S{2}/', # line break
        );
        $replace = array(
            '<a href="$1">$1</a>',
            '<a href="mailto:$1">$1</a>',
            // '- $1'
        );

        return preg_replace($pattern, $replace, $string);
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
     * @return $this
     */
    public function bind($key, & $value)
    {
        $this->{$key} =& $value;

        return $this;
    }

    /* ---- generic helpers --- */

    public function hasMany($mix)
    {
        if (is_string($mix) && isset($this->{$mix})) {
            return count($this->{$mix})>1;
        } elseif (is_array($mix)) {
            return count($mix)>1;
        }

        return false;
    }

    /**
     * Returns this view model layout.
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->_layout;
    }

}
