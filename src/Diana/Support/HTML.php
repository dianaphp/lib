<?php
/**
 * Created by PhpStorm.
 * User: Antonio Ianzano
 * Date: 26.11.2017
 * Time: 03:26
 */

namespace Diana\Support;

class HTML
{
    /**
     * Converts an array into a string of css
     * @param array $array
     * @return string
     */
    public static function css(array $array)
    {
        $css = '';
        foreach ($array as $key => $value)
            $css .= $key . ':' . $value . ';';
        return $css;
    }
}