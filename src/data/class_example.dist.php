<?php
// This is an example using a class definition
// -------------------------------------------
//
// The resource class definition woudl be define as such:
//
// $c['resources'] = array(
//   '/version/:software' => array(
//      'controller' => array(
//          'name' => 'myNamespace\SoftwareDownload',
//           'args' => null
//       )
//   ),
//   '/download/:software' => array(
//       'controller' => array(
//           'name' => 'myNamespace\SoftwareDownload',
//           'args' => null
//       )
//  ),
//   '/upload/:software' => array(
//       'controller' => array(
//           'name' => 'myNamespace\SoftwareDownload',
//           'args' => null
//       )
//  )
// };

namespace myNamespace;

use Apix;

/**
 * Software Download API
 *
 * This is just an exmaple of software download API.
 *
 * @api_public      true
 * @api_version     1.0
 * @api_permission  admin
 * @api_randomName  classRandomValue
 */
class SoftwareDownload
{

    /**
     * Returns the lastest version of the :software
     *
     * @param       string  $software
     * @return      array       The array to return to the client
     * @api_role    public      Available to all!
     * @api_cache   10w softy   Cache for a maximum of 10 weeks
     *                          and tag cache buffer as 'mySoftware'.
     */
    public function onRead($software)
    {
        // ...
        return array(
            $software => 'the version string of software.'
        );
    }

    /**
     * Download the :software...
     *
     * @param       string  $software
     * @return      string              Output the binary & quit.
     * @api_role    public              Available to all!
     * @api_cache   10w softy           Cache for a maximum of 10 weeks
     *                                  and tag cache buffer as 'mySoftware'.
     */
    public function onRead($software)
    {
        // ...
        echo $file;
        exit; // to stop the server handling the response anyfurther.
    }

    /**
     * Upload a new software :software
     *
     * @param               Request  $request   The Server Request object.
     * @param               string   $software
     * @return              array               A reponse array.
     * @api_role            admin               Require admin priviledge
     * @api_purge_cache     mySoftware          Purge the cache of all the
     *                                          'mySoftware' tagged entries.
     */
    public function onCreate(Request $request, $software)
    {
        // ...
    }

    /**
     * Update an existing software :software
     *
     * @see POST /upload/:software
     */
    public function onUpdate($software)
    {
        // ...
    }

}