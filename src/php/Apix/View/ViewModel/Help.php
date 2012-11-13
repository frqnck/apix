<?php
namespace Apix\View\ViewModel;

use Apix\View\ViewModel;

class Help extends ViewModel
{
    //public $_layout = 'help';

    // -- Shared
    public $help_path = 'http://zenya.dev/index2.php/api/v1/help';
    // -- Shared

    public function getFullToc()
    {
        return array(
            array('name'=>'test 1', 'url'=>'#1', 'on'=>true),
            array('name'=>'test 2', 'url'=>'#2', 'on'=>true),
            array('name'=>'test 3', 'url'=>'#3', 'on'=>true),
        );
    }

    // deals with params definitions
	public function params()
	{
		$many = $this->hasMany('params');
		return array(
            'title' => $many ? 'Options' : 'Option',
            'txt'   => $many
            			? 'The following options are available:'
            			: 'The following option is available:',
            'items' => array_values($this->params)
        );
	}

    // deals with usage definitions
    public function usage()
    {
        if(!isset($this->usage)) {
            return null;
        }

        // if(is_array($this->usage)) {
            // array_values($this->params)
            return array('items' => array(
                'method'    =>  'todo',
        //         'path'      =>  '/hhh1',
        //         'txt'       =>  $this->usage
            ));
        // }
    }

	// deals with groups definitions
	public function groups()
	{
        $ignore = array('internal', 'id', 'toc', 'todo', 'method');
        $titles = array(
            'return'    => 'Return',
            'example'   =>  $this->hasMany('example') ? 'Examples' : 'Example',
            'copyright' => 'Copyright',
            'see'       => 'See Also',
            #'link'      => $this->hasMany('link') ? 'Links' : 'Link',
        );
        $groups = array();
        foreach($titles as $key => $title) {
            if(isset($this->{$key})
                #&& !in_array($key, $ignore)
            ) {
                $groups[] = array('title' => $title, 'items' => (array) $this->{$key});
            }
        }
        return $groups;
    }

}