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

class Help extends Common
{
    /**
     * Holds the supported format definitions.
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
     * Holds the usage string.
     * @var string
     */
    protected $usage = null;

    /**
     * Gets the view layout.
     *
     * @return string
     */
    public function getLayout()
    {
        return isset($this->items) ? 'man_toc' : 'man_page';
    }

    /**
     * Returns the Usage field formatted
     *
     * @return string
     */
    public function getFormatedUsage()
    {
        if (null === $this->usage) {
            $this->usage = '<dl><dt class="flush">'
                            // . $this->path
                            . $this->options['rel_path'];

            if(isset($this->params)) {
                foreach($this->params as $var) {
                    if(!$var['required'])
                        $this->usage .= '/:' . $var['name'];
                }
            }
            $this->usage .= '</dt><dd>';
            if(isset($this->globals)) {
                foreach($this->globals as $param) {
                    $types = explode('|', $param['type']);
                    if(! in_array('null', $types) ) {
                        $this->usage .= '<span>' . $param['name'];
                        if($param['type'] != 'null') {
                            $this->usage .= '=<i>' . $param['type'] . '</i>';
                        }
                        $this->usage .= '</span>';
                    }
                }
            }
            $this->usage .= '</dd></dl>';
        }

        return $this->usage;
    }

    /**
     * Checks wether the plugin signature is enable.
     *
     * @return boolean
     */
    public function hasPluginSignature()
    {
        return in_array('Apix\Plugin\OutputSign', $this->config['plugins']);
    }

    /**
     * Checks wether many output format are available.
     *
     * @return boolean
     */
    public function hasManyOutputFormats()
    {
        return $this->config['routing']['formats'] > 1;
    }

    /**
     * Returns the formatted Output formats.
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
                '<td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td>',
                $item['url'], strtoupper($k),
                $item['name'], $item['ext'], $item['mime']
            );
        }

        return $formatted;
    }

    // public function getOutputExtensionExamples()
    // {
    //     $formats = &$this->format_defs;
    //     $formatted = array();
    //     sort($this->config['routing']['formats']);
    //     foreach($this->config['routing']['formats'] as $k) {
    //         $ext = $formats[$k]['ext'];
    //         $formatted[] = sprintf(
    //             '<a href="/help/:path%s">/help<b>%s</b></a>', $ext, $ext
    //         );
    //     }

    //     return $formatted;
    // }

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
    public function getPathParams()
    {
        if (isset($this->params) && !empty($this->params)) {
            return array(
                'prefix' => ':',
                'items'  => array_values($this->params)
            );
        }
    }

    /**
     * Deals with the request params/filters definitions.
     *
     * @return array
     */
    public function getQueryParams()
    {
        if (isset($this->globals) && !empty($this->globals)) {
            return array(
                'prefix' => '&',
                'items' => array_values($this->globals)
            );
        }
    }

    /**
     * Deals with the request params/filters definitions.
     *
     * @return array
     */
    public function getReturns()
    {
        if (isset($this->return) && !empty($this->return)) {
            return array(
                'items' => array_values($this->return)
            );
        }
    }

    /**
     * Deals with the request params/filters definitions.
     *
     * @return array
     */
    public function getVersion()
    {
        if (isset($this->version)) {
            if(is_array($this->version)) {
                $this->version = 'version <b>'
                        . implode('</b>, version <b>', $this->version)
                        . '</b>';
            }

            return $this->version;
        }
    }

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
            'license'   => 'Licensing',
            // 'throws'    => 'Failure responses'
        );
        $groups = array();

        foreach ($titles as $key => $title) {
            if(
                isset($this->{$key}) #&& !in_array($key, $ignore)
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
