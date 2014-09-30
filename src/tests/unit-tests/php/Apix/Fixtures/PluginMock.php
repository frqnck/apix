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

namespace Apix\Fixtures;

use Apix\Plugin\PluginAbstract;

class PluginMock extends PluginAbstract
{
    public static $hook = array('level', 'type');

    public $value = null;
    public $integer = 0;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct($value=null)
    {
        $this->value = $value;
        ++$this->integer;
    }

    /**
     * Observer
     *
     * @param Pattern\Subject $errorHandler
     */
    public function update(\SplSubject $subject)
    {
        return $this->value;
    }

}
