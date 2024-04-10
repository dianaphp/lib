<?php

namespace Diana\Support\Helpers;

use Closure;

class Data
{
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @param  mixed  ...$args
     * @return mixed
     */
    public static function valueOf($value, ...$args)
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}