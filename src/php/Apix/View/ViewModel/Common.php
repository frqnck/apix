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
     * Config variable exposed to the templates.
     * @var array|null
     */
    public $config = null;

    /**
     * Option variable exposed to the templates.
     * @var array
     */
    public $options = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = Service::get('config');
    }

    /**
     * Returns the API resources.
     *
     * @return array
     */
    public function getResources()
    {
    	if( $this->items ) {
    		$items = $this->items;
        } else {
        	$help = new Help();
        	$server =  Service::get('server');
			// var_dump($items);

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
     * Get the man
     *
     * @return string
     */
    public function getCmdSection()
    {
    	static $out; 
		if(!$out) {
	    	switch($this->getLayout()):
	    		case 'man_error': $section = 7; break;
	    		case 'man_page': $section = 3; break;
	    		default: $section = 1;
	    	endswitch;
	        $out = sprintf('%s(%s)', $this->config['output_rootNode'], $section);
    	}
        return $out;
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

}