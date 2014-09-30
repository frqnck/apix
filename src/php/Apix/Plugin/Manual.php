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

namespace Apix\Plugin;

use Apix\View\View;

class Manual extends PluginAbstract
{

    public static $hook = array('response', 'early');

    /**
     * Constructor.
     *
     * @param array $options Array of options.
     */
    public function __construct(array $options=null)
    {
        $this->setOptions($options);
    }

    public function update(\SplSubject $response)
    {
        if (
            'html' !== $response->getFormat()
             || 'help' != key($response->results)
        ) {
            return false;
        }

        $view = new View($response->results);
        $response->setOutput(
            $view->render()
        );
    }

}
