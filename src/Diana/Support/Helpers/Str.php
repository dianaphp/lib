<?php

namespace Diana\Support\Helpers;


final class Str
{
    public static function formatClass(string $string, string $separator = ':'): string
    {
        return implode('\\', array_map(fn($segment) => ucfirst(static::camel($segment)), explode($separator, $string)));
    }

    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel(string $string): string
    {
        return static::lcfirst(static::studly($string));
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly(string $string): string
    {
        $words = explode(' ', static::replace(['-', '_'], ' ', $string));

        $studlyWords = array_map(fn($word) => static::ucfirst($word), $words);

        return implode($studlyWords);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @return bool
     */
    public static function startsWith(string $haystack, string|iterable $needles): bool
    {
        if (!is_iterable($needles))
            $needles = [$needles];

        foreach ($needles as $needle) {
            if ((string) $needle !== '' && str_starts_with($haystack, $needle))
                return true;
        }

        return false;
    }

    /**
     * Make a string's first character uppercase.
     *
     * @param  string  $string
     * @return string
     */
    public static function ucfirst(string $string, string $encoding = 'UTF-8'): string
    {
        return static::upper(static::substr($string, 0, 1, $encoding), $encoding) . static::substr($string, 1, null, $encoding);
    }

    //Convert the given string to upper-case.
    public static function upper(string $string, string $encoding = 'UTF-8'): string
    {
        return mb_strtoupper($string, $encoding);
    }

    // Make a string's first character lowercase.
    public static function lcfirst(string $string, string $encoding = 'UTF-8')
    {
        return static::lower(static::substr($string, 0, 1, $encoding), $encoding) . static::substr($string, 1, null, $encoding);
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $string
     * @return string
     */
    public static function lower(string $string, string $encoding = 'UTF-8')
    {
        return mb_strtolower($string, $encoding);
    }

    /**
     * Returns the portion of the string specified by the start and length parameters.
     *
     * @param  string  $string
     * @param  int  $start
     * @param  int|null  $length
     * @param  string  $encoding
     * @return string
     */
    public static function substr($string, $start, $length = null, $encoding = 'UTF-8')
    {
        return mb_substr($string, $start, $length, $encoding);
    }

    /**
     * Determine if a given string contains a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @param  bool  $ignoreCase
     * @return bool
     */
    public static function contains($haystack, $needles, $ignoreCase = false)
    {
        if ($ignoreCase) {
            $haystack = mb_strtolower($haystack);
        }

        if (!is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ($ignoreCase) {
                $needle = mb_strtolower($needle);
            }

            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Replace the given value in the given string.
     *
     * @param  string|iterable<string>  $subject
     * @param  string|iterable<string>  $search
     * @param  string|iterable<string>  $replace
     * @param  bool  $caseSensitive
     * @return string|string[]
     */
    public static function replace($search, $replace, $subject, $caseSensitive = true)
    {
        return $caseSensitive
            ? str_replace($search, $replace, $subject)
            : str_ireplace($search, $replace, $subject);
    }

    /**
     * Return the remainder of a string after the first occurrence of a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     */
    public static function random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytesSize = (int) ceil($size / 3) * 3;

            $bytes = random_bytes($bytesSize);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|iterable<string>  $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        if (!is_iterable($needles)) {
            $needles = (array) $needles;
        }

        foreach ($needles as $needle) {
            if ((string) $needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the portion of a string before the first occurrence of a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function before($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $result = strstr($subject, (string) $search, true);

        return $result === false ? $subject : $result;
    }

    /**
     * Convert a string to kebab case.
     *
     * @param  string  $string
     * @return string
     */
    public static function kebab($string)
    {
        return static::snake($string, '-');
    }

    /**
     * Return the length of the given string.
     *
     * @param  string  $string
     * @param  string|null  $encoding
     * @return int
     */
    public static function length($string, $encoding = null)
    {
        return mb_strlen($string, $encoding);
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $string
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($string, $delimiter = '_')
    {
        if (!ctype_lower($string)) {
            $string = preg_replace('/\s+/u', '', ucwords($string));

            $string = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $string));
        }

        return $string;
    }

    /**
     * Get the portion of a string before the last occurrence of a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    public static function beforeLast($subject, $search)
    {
        if ($search === '') {
            return $subject;
        }

        $pos = mb_strrpos($subject, $search);

        if ($pos === false) {
            return $subject;
        }

        return static::substr($subject, 0, $pos);
    }

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $cap
     * @return string
     */
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
    }

    /**
     * Begin a string with a single instance of a given value.
     *
     * @param  string  $value
     * @param  string  $prefix
     * @return string
     */
    public static function start($value, $prefix)
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }
}