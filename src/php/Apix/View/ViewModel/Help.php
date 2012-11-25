<?php
namespace Apix\View\ViewModel;

use Apix\View\ViewModel;

class Help extends ViewModel
{

    // -- Shared
    public $help_path = 'http://zenya.dev/index2.php/api/v1/help';
    // -- Shared

    /**
     * Gets the view layout.
     *
     * @return string
     */
    public function getViewLayout()
    {
        if(isset($_GET['debug'])) $this->debug();

        switch(true) {
            case isset($this->items):
                return 'man_toc';
            case isset($this->methods):
                return 'man_group';
            default:
                return 'man_single';
        }
    }

    public function items()
    {
        // remove index key for mustashe.
        // TODO: make this a view helper.
        foreach($this->items as $item) {
            foreach($item['methods'] as $k => $v) {
                $item['methods'][$k]['method'] = $k;
                $item['methods'][] = $item['methods'][$k];
                unset($item['methods'][$k]);
            }
            $this->items[] = $item;
        }

        return $this->items;
    }

    /**
     * Deals with parameter' definitions.
     *
     * @return array
     */
    public function params()
    {
        if(empty($this->params)) {
            return null;
        }

        $params = isset($this->params) ? $this->params : array();

        $many = $this->hasMany('params');
        return array(
            'title' => $many ? 'Options' : 'Option',
            'txt'   => $many
                    ? 'The following request parameters are available:'
                    : 'The following request parameter is available:',
            'items' => array_values($params)
        );
    }

    /**
     * Deals with group' definitions.
     *
     * @return array
     */
	public function groups()
	{
        #$ignore = array('internal', 'id', 'toc', 'todo', 'method');
        $titles = array(
            'return'        => 'Response',
            'example'       => $this->hasMany('example') ? 'Examples' : 'Example',
            'copyright'     => 'Copyright',
            'see'           => 'See also',
            'link'          => $this->hasMany('link') ? 'Links' : 'Link',
        );
        $groups = array();

        foreach($titles as $key => $title) {
            if(
                isset($this->{$key})
                #&& !in_array($key, $ignore)
            ) {
                $groups[] = array(
                    'title' => $title,
                    'items' => (array) $this->get($key)
                );
            }
        }

        return $groups;
    }

    /**
     * _def - view helper.
     *
     * @return string
     */
    public function _def()
    {
        return function($t) {
            return '<span class="default">' . $t . '</span>';
        };
    }

}