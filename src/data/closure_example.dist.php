<?php
// This is an example using a closure definition
// ---------------------------------------------
//
// Closure is exerimental... avoid using for now.

require_once 'apix.phar';

try {
    $api = new Apix\Server(require 'config.php');

    /**
     * Returns the lastest version of the :software
     *
     * @param  string $software
     * @return array  The array to return to the client
     * @api_role    public          Available to all!
     * @api_cache   10w some_name   Cache for a maximum of 10 weeks
     *                              and tag cache buffer as 'some_name'.
     */
    $api->onRead('/version/:software', function($software) {
        // ...
        return array(
            $software => 'the version string of software.'
        );
    });

    /**
     * Download some :software
     *
     * @param  string          $software
     * @return string          Output the binary & quit.
     * @throws DomainException 404
     * @api_role    public              Available to all!
     * @api_cache   10w some_name       Cache for a maximum of 10 weeks
     *                                  and tag cache buffer as 'some_name'.
     */
    $api->onRead('/download/:software', function($software) {
        // ...
        if (file_exists($software)) {
            echo file_get_contents($software);
            exit; // to stop the server handling the response anyfurther.
        }

        throw new DomainException("\"$software\" doesn't not exist.", 404);
    });

    /**
     * Upload a new software :software
     *
     * @param  Request $request  The Server Request object.
     * @param  string  $software
     * @return array   A reponse array.
     * @api_role            admin               Require admin priviledge
     * @api_purge_cache     some_name           Purge the cache of all the
     *                                          'some_name' tagged entries.
     */
    $api->onCreate('/upload/:software', function(Request $request, $software) {
        // ...
    });

    /**
     * Update an existing software :software
     *
     * @see POST /upload/:software
     */
    $api->onUpdate('/upload/:software', function($software) {
        // ...
    });

    echo $api->run();

} catch (\Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    die("<h1>500 Internal Server Error</h1>" . $e->getMessage());
}
