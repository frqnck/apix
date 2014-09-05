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

namespace MY_NAMESPACE;

use Apix\Server,
    Apix\Exception;

// Composer autoloader 
require_once '../vendor/autoload.php';

// Uncomment if using apix.phar
// require '../apix.phar';

try {
    $api = new Server('../my_api_config.php');

    $api->onRead('/welcome/:name',
        /**
         * A simple Hello World example...
         *
         * @api_auth  groups=public users=franck
         * @api_cors  enable=true host=.+\.domain\.(com|co\.uk)
         * @param     string  $name  The name to say hi to.
         * @return    array   The "Hello World" result set.
         */
        function ($name = 'World')
        {
            $results = array(
                "Hello" . ucfirst($name)
            );

            return $results;
        });

    echo $api->run();

} catch (\Exception $e) {
    Exception::startupException($e);
}