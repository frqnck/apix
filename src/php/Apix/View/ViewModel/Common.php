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

namespace Apix\View\ViewModel;

use Apix\View\ViewModel;
use Apix\Service;
use Apix\Resource\Help;

class Common extends ViewModel
{

    /**
     * Returns the API resources.
     *
     * @return array
     */
    public function getResources()
    {
        if ($this->items) {
            $items = $this->items;
        } else {
            $help = new Help();
            $server =  Service::get('server');
            $items = $help->getResourcesDocs($server);
        }

        $resources = array();
        foreach ($items as $resource) {
            foreach ($resource['methods'] as $v) {
                if(
                    !isset($v['apix_man_toc_hidden'])
                ) {
                    $resources[] = array(
                        'method'   => $v['method'],
                        'resource' => $resource['path'],
                        'querystr' => $v['method'] !== 'GET'
                                        ? '?method=' . $v['method']
                                        : null
                    );
                }
            }
        }

        return $resources;
    }

    /**
     * Deals with the resource groups definitions.
     *
     * @return array
     */
    public function getResourceGroups()
    {
        #$ignore = array('internal', 'id', 'toc', 'todo', 'method');
        $titles = array(
            'example'   => $this->hasMany('example') ? 'Examples' : 'Example',
            'see'       => 'See also',
            'link'      => $this->hasMany('link') ? 'Links' : 'Link',
            'copyright' => 'Copyright',
            'license'   => 'Licensing'
        );
        $groups = array();

        foreach ($titles as $key => $title) {
            if(
                isset($this->{$key}) || isset($this->options[$key])
                #&& !in_array($key, $ignore)
            ) {
                $groups[] = array(
                    'title' => $title,
                    'items' => $this->get($key)
                );
            }
        }

        return $groups;
    }

    /**
     * Returns the man index/section string.
     *
     * @return integer
     */
    public function getManTocSection()
    {
        static $str;
        if (!$str) {
            switch($this->getLayout()):
                case 'man_error': $section = 7; break;
                case 'man_page': $section = 3; break;
                default: $section = 1;
            endswitch;
            $str = sprintf('%s(%s)', $this->config['output_rootNode'], $section);
        }

        return $str;
    }

    /**
     * _def - view helper.
     *
     * @return string
     */
    public function _def()
    {
        return function ($t) {
            return '<span class="default">' . $t . '</span>';
        };
    }

    public function debug($data=null)
    {
        echo '<pre>';
        print_r(  null !== $data ? $data : $this );
        echo '</pre>';
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
