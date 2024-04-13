<?php

namespace Diana\Support\Helpers;

class Emit
{
    public static function e($value, $doubleEncode = true)
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }
}