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
     * Returns the lastest version number of :software
     *
     * @param       string      $software   The name of the software.
     * @return      array                   The array to return to the client.
     * @api_role    public                  This API does not need ACL/AUTH (default).
     * @api_cache   10w softy               This will be cached for a maximum of 10 weeks.
     *                                      and tagged as 'softy'.
     */
    public function onRead($software)
    {
        // ...
        return array(
            $software => 'the version string of software.'
        );
    }

    /**
     * Returns the :software data to be downloaded by the client.
     *
     * @param       string      $software   The name of the software.
     * @return      string                  Output the binary & quit.
     * @api_cache   10w softy               Cache for a maximum of 10 weeks
     *                                      and tagged as 'softy'.
     */
    public function onRead($software)
    {
        // ... $file = filename
        header('Content-Type: "application/octet-stream"');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Content-Length: '.filesize($file));
        header('Pragma: no-cache');

        if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) {
            header('Cache-Control: must-revalidate, no-cache, no-store, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        $data = readfile($file);
        exit($data); // stop the server handling the response anyfurther.
    }

    /**
     * Upload a new :software
     *
     * @param               Request  $request   The current Apix Request object.
     * @param               string   $software  The name of the software.
     * @return              array               An array to return to the client.
     * @api_role            admin               This API has an ACL for admin.
     * @api_purge_cache     mySoftware          Purge the cache of all the
     *                                          'softy' tagged entries.
     */
    public function onCreate(Request $request, $software)
    {
        // ...
    }

    /**
     * Update an existing :software
     *
     * @see     self::onCreate
     */
    public function onUpdate($software)
    {
        // ...
    }

}