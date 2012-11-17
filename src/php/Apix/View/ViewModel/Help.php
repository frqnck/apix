<?php
namespace Apix\View\ViewModel;

use Apix\View\ViewModel;

class Help extends ViewModel
{
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

    public function params()
    {
       if(empty($this->params)) return null;
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

    public function _def()
    {
        return function($t) {
            return '<span class="default">' . $t . '</span>';
        };
    }

    //public $_layout = 'help';

    // -- Shared
    public $help_path = 'http://zenya.dev/index2.php/api/v1/help';
    // -- Shared

    public function description()
    {
        return $this->get('description');
    }

	// deals with groups definitions
	public function groups()
	{
        $ignore = array('internal', 'id', 'toc', 'todo', 'method');
        $titles = array(
            'return'    => 'Return',
            'example'   =>  $this->hasMany('example') ? 'Examples' : 'Example',
            'copyright' => 'Copyright',
            'see'       => 'See also',
            #'link'      => $this->hasMany('link') ? 'Links' : 'Link',
        );
        $groups = array();

        foreach($titles as $key => $title) {
            if(isset($this->{$key})
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

}