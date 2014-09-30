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

/**
 * BlankResource
 *
 * This is just a blank resource. Use for testing and demo.
 *
 * @api_public true
 * @api_version 1.0
 * @api_permission admin
 * @api_randomName classRandomValue
 */
class BlankResource
{
    /*
     * A public var.
     */
    public $hello = 'World!!!';

    /*
     * Another public var.
     */
    public $results = array();

    /*
     * A protected var.
     */
    protected $_protected = 'protected';

    /*
     * A private var
     */
    protected $_private = 'private';

    /**
     * constructor
     *
     * @param  array $params
     * @return void
     */
    public function __construct(array $params)
    {
        $this->constructorParams = get_defined_vars();
    }

    /**
     * GET method
     *
     * @param  string $keyword
     * @param  string $param2   with comments
     * @param  mixed  $optional
     * @return array
     * @api_role admin
     */
    public function onRead($keyword, $param2=null, $optional=null)
    {
        return array(
            'class'            => __CLASS__,
            'constructorParams'    => $this->constructorParams,
            'method'            => __METHOD__,
            'methodParams'        => get_defined_vars()
        );
    }

    /**
     * UPDATE method
     *
     * @param  integer $param1
     * @return array
     */
    public function onUpdate(integer $param1)
    {
        return array('method'=>__METHOD__, 'params' => $params);
    }

    /**
     * TEST method
     *
     * @return array
     */
    public function onTest()
    {
        return array('TEST');
    }

}
