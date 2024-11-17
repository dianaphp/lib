<?php

use Diana\Support\Wrapper\ArrayWrapper;

if (!function_exists('arr')) {
    function arr(mixed ...$input): ArrayWrapper
    {
        return new ArrayWrapper(...$input);
    }
}
