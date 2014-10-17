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

class Help extends ViewModel
{
    /**
     * @var array
     */
    protected $format_defs = array(
        'post' => array(
            'url'  => 'http://en.wikipedia.org/wiki/POST_%28HTTP%29',
            'mime' => 'application/x-www-form-urlencoded',
            'ext'  => null,
            'input' => 'encoded key-value pairs.',
        ),
        'html' => array(
            'url'  => 'http://en.wikipedia.org/wiki/HTML',
            'name' => 'HyperText Markup Language',
            'mime' => 'text/html', // application/xhtml+xml
            'ext'  => '.html'
        ),
        'json' => array(
            'url'  => 'http://en.wikipedia.org/wiki/Json',
            'name' => 'JavaScript Object Notation',
            'mime' => 'application/json',
            'ext'  => '.json',
        ),
        'jsonp' => array(
            'url'  => 'http://en.wikipedia.org/wiki/JSONP',
            'name' => 'JSON with padding',
            'mime' => 'application/javascript', // one day: application/json-p
            'ext'  => '.jsonp'
        ),
        'php' => array(
            'url'  => 'http://php.net/manual/en/function.serialize.php',
            'name' => 'Byte-stream representation',
            'mime' => 'text/plain',
            'ext'  => '.php'
        ),
        'xml' => array(
            'url'  => 'http://en.wikipedia.org/wiki/XML',
            'name' => 'Extensible Markup Language',
            'mime' => 'text/xml', // application/xhtml+xml
            'ext'  => '.xml',
        ),
        'csv' => array(
            'url'  => 'http://en.wikipedia.org/wiki/Comma-separated_values',
            'name' => 'Comma-separated values',
            'mime' => 'text/xml', // application/xhtml+xml
            'ext'  => '.csv'
        )
    );

    /**
     * Gets the view layout.
     *
     * @return string
     */
    public function getViewLayout()
    {
        if(isset($_GET['debug'])) $this->debug();

        switch (true) {
            case isset($this->items):
                return 'man_toc';

            case isset($this->methods):
                return 'man_group';

            default:
                return 'man_single';
        }
    }


    /**
     * Returns the Usage field formatted
     *
     * @return string
     */
    public function getFormatedUsage()
    {
        if (!isset($this->usage)) {
            $this->usage = '<dl><dt class="flush">' . $this->path;

            foreach($this->params as $var) {
                if(!$var['required'])
                    $this->usage .= '/' . $var['name'];
            }
            $this->usage .= '</dt><dd>';
            if(isset($this->globals)) {
                foreach($this->globals as $param) {
                    $this->usage .= '<span>' . $param['name'];
                    if($param['type'] != 'null') $this->usage .= '=<i>' . $param['type'] . '</i>';
                    $this->usage .= '</span>';
                }
            }
            $this->usage .= '</dd></dl>';

            // $vars = array_filter($this->params, function($v) {
            //     return !$v['required'];
            // });

            // var_dump( $this->globals ); exit;


        // request params;
// var_dump($this->params);
        }

        return $this->usage;
    }

    /**
     * Returns formatted Input formats
     *
     * @return array
     */
    public function getResources()
    {
        $resources = array();
        foreach ($this->items as $resource) {
            foreach ($resource['methods'] as $v) {
                if(
                    !isset($v['apix_man_toc_hidden'])
                ) {
                    $resources[] = array(

                        // 'url' => $this->options['url']
                        'method' => $v['method'],
                        'resource'   => $resource['path']
                    );
                }
            }
        }
        return $resources;
    }

    public function hasPluginSignature()
    {
        return in_array('Apix\Plugin\OutputSign', $this->config['plugins']);
    }

    /**
     * Returns formatted Output formats
     *
     * @return array
     */
    public function getOutputFormats()
    {
        $formatted = array();
        sort($this->config['routing']['formats']);
        foreach($this->config['routing']['formats'] as $k) {
            $item = &$this->format_defs[$k];
            $formatted[] = sprintf(
                '<a href="%s"><b>%s</b></a> <dfn>%s</dfn> %s',
                $item['url'],
                strtoupper($k),
                $item['name'],
                $k == $this->config['routing']['default_format'] ? '(default format)' : ''
            );
        }

        return $formatted;
    }

    public function getOutputExtensionExamples()
    {
        $formats = &$this->format_defs;
        $formatted = array();
        sort($this->config['routing']['formats']);
        foreach($this->config['routing']['formats'] as $k) {
            $ext = $formats[$k]['ext'];
            $formatted[] = sprintf(
                '<a href="/help/:path%s">/help<b>%s</b></a>', $ext, $ext
            );
        }

        return $formatted;
    }


    /**
     * Returns formatted Input formats
     *
     * @return array
     */
    public function getInputFormats()
    {
        $formats = &$this->format_defs;
        $formatted = array();
        foreach($this->config['input_formats'] as $k) {
            $formatted[] = sprintf(
                '"<var>%s</var>" for <a href="%s">%s</a> %s',
                isset($formats[$k]['mime']) ? $formats[$k]['mime'] : 'n/a',
                isset($formats[$k]['url']) ? $formats[$k]['url'] : '#',
                $k != 'post' ? strtoupper($k) : '',
                isset($formats[$k]['input']) ? $formats[$k]['input'] : 'formatted data.'
            );
        }

        return $formatted;
    }

    /**
     * Deals with the resource parameters definitions.
     *
     * @return array
     */
    public function getResourceParams()
    {
        if (isset($this->params) && !empty($this->params)) {
            $many = $this->hasMany('params');
            return array(
                'prefix' => ':',
                'title' => 'Resource ' . ($many ? 'variables' : 'variable'),
                'txt'   => $many
                        ? 'The following resource variables are available:'
                        : 'The following resource variable is available:',
                'items' => array_values($this->params)
            );
        }
    }

    /**
     * Deals with the request params/filters definitions.
     *
     * @return array
     */
    public function getRequestParams()
    {
        if (isset($this->globals) && !empty($this->globals)) {
            $many = $this->hasMany('globals');
            return array(
                'usage_prefix' => '&',
                'title' => 'Request ' . ($many ? 'parameters' : 'parameter'),
                'txt'   => $many
                        ? 'The following request parameters are available:'
                        : 'The following request parameter is available:',
                'items' => array_values($this->globals)
            );
        }
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
            'return'        => 'Expected Response',
            'example'       => $this->hasMany('example') ? 'Examples' : 'Example',
            'copyright'     => 'Copyright',
            'see'           => 'See also',
            'link'          => $this->hasMany('link') ? 'Links' : 'Link',
            'license'       => 'License'
        );
        $groups = array();

        foreach ($titles as $key => $title) {
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

}
