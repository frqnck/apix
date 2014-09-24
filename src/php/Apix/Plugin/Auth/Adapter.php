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

namespace Apix\Plugin\Auth;

interface Adapter
{

    /**
     * Performs an authentication attempt.
     *
     * @link    http://www.peej.co.uk/projects/phphttpdigest.html
     * @link    http://www.faqs.org/rfcs/rfc2617.html
     *
     * @return mixed Either the username of the user making the request or we
     *               return access to $this->send() which will pop up the authentication
     *               challenge once again.
     */
    public function authenticate();

    /**
     * Sends/sets the HTTP Auth header.
     *
     * @return void
     */
    public function send();

    /**
     * Returns the Auth token to match against.
     *
     * @param  array                  $auth_data An array of authentification data.
     * @return boolean|string|object.
     */
    public function getToken(array $auth_data);

}
