<?php

namespace Apix\Fixtures;

/**
 * Auth Resource
 *
 * Testign Auth
 *
 * @api_version 1.0
 */
class AuthResource
{
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
     * Auth GET method
     *
     * @param  string $param
     * @param  mixed  $optional
     * @return array
     *
     * @api_role public
     */
    public function onRead($username, $optional=null)
    {
        return array(
            'class' => __CLASS__,
            'constructorParams' => $this->constructorParams,
            'method' => __METHOD__,
            'methodParams' => get_defined_vars()
        );
    }

    /**
     * Auth POST method
     *
     * @param  integer $param
     * @return array
     *
     * @api_role admin
    */
    public function onCreate($username)
    {
        return array('method'=>__METHOD__);
    }

    /**
     * Auth PUT method
     *
     * @param  integer $param
     * @return array
     *
     * @api_role admin
    */
    public function onUpdate($params)
    {
        return array('method'=>__METHOD__, 'params' => $params);
    }

}
