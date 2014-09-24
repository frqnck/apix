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

namespace Apix;

class Session
{

    /**
     * Holds the session's username.
     * @var string
     */
    protected $username;

    /**
     * Holds this user's group.
     * @var string
     */
    protected $group;

    /**
     * Holds the user's trusted IPs.
     * @var array
     */
    protected $ips = null;

    /**
     * Holds some user's arbitrary data.
     * @var array
     */
    protected $data = array();

    /**
     * Constructor will set the session for the given username.
     *
     * @param string      $username
     * @param string|null $group
     */
    public function __construct($username, $group = null)
    {
        $this->username = $username;
        $this->group = $group;

        // session_start();
    }

    /**
     * Returns the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the user's group.
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the user's group.
     *
     * @param  string $group
     * @return void
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Returns the trusted IPs.
     *
     * @return array
     */
    public function getTrustedIps()
    {
        return $this->ips;
    }

    /**
     * Sets the trusted IPs.
     *
     * @param  array $ips
     * @return void
     */
    public function setTrustedIps(array $ips=null)
    {
        $this->ips = $ips;
    }

    /**
     * Check if the specified user data is set.
     *
     * @param  string  $group
     * @return boolean
     */
    public function hasTrustedIps()
    {
        return null !== $this->ips;
    }

    /**
     * Adds arbitrary session data.
     *
     * @param  string $group
     * @return void
     */
    public function addData($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Checks wether the specified key is set in the session dataset.
     *
     * @param  string  $group
     * @return boolean
     */
    public function hasData($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Returns the specified data -- or the whole dataset if null.
     *
     * @param  string     $key
     * @return mixed|null
     */
    public function getData($key=null)
    {
        if(null === $key) return $this->data;

        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

}
