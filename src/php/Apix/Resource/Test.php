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

namespace Apix\Resource;

class Test
{

    /**
     * HTTP HEAD: test action handler
     *
     * The HEAD method is identical to GET except that the server MUST NOT return
     * a message-body in the response. The metainformation contained in the HTTP
     * headers in response to a HEAD request SHOULD be identical to the information
     * sent in response to a GET request. This method can be used for obtaining
     * metainformation about the entity implied by the request without transferring
     * the entity-body itself. This method is often used for testing hypertext links
     * for validity, accessibility, and recent modification.
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
     *
     * @return null
     * @cacheable true
     * @codeCoverageIgnore
     * @apix_man_toc_hidden
     */
    final public function onTest()
    {
        // identical to a GET request without the body output.
        // shodul proxy to the get method
        return array(); // MUST NOT return a message-body in the response
    }

}
