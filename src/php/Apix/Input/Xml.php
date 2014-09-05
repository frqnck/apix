<?php
namespace Apix\Input;

class Xml implements InputInterface
{

    /**
     * @var	string
     */
    public $encoding = 'utf-8';

    /**
     * {@inheritdoc}
     */
    public function decode($xmlStr, $assoc=true)
    {

        /*
            $array = json_decode(json_encode($xmlStr), true);

            foreach ( array_slice($array, 0) as $key => $value ) {
                if ( empty($value) ) $array[$key] = NULL;
                elseif ( is_array($value) ) $array[$key] = toArray($value);
            }

            return $array;
        */

        // json_decode only works with UTF-8!!!
        return json_decode(json_encode((array) simplexml_load_string($xmlStr)), $assoc);
    }

}
