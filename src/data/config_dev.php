<?php
// This is the Apix config.dev.php file

namespace Apix;

if(!defined('DEBUG')) define('DEBUG', true);

$c = array(
    'api_version'       => '0.1.0.bash',
    'api_realm'         => 'api.domain.tld',
    'output_rootNode'   => 'apix',
    'input_formats'     => array('post', 'json', 'xml'),

    'routing'           => array(
        'path_prefix'       => '/^(\/\w+\.\w+)?(\/api)?\/v(\d+)/i',
        'formats'           => array('json', 'xml', 'jsonp', 'html', 'php'),
        'default_format'    => 'json',
        'http_accept'       => true,
        'controller_ext'    => true,
        'format_override'   => isset($_REQUEST['_format'])
                                ? $_REQUEST['_format']
                                : false,
    )
);

$c['init']['zlib.output_compression'] = false;

$c['config_path'] = __DIR__;

return $c;
