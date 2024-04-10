<?php

namespace Diana\Support\Helpers;

use Diana\Support\Collection\Collection;

class Arr
{
    /**
     * Return the last element in an array passing a given truth test.
     *
     * @param  array  $array
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public static function last(iterable $array, callable $callback = null, $default = null)
    {
        $lastKey = array_key_last($array);
        if (!$lastKey)
            return Data::valueOf($default);

        if (is_callable($callback))
            $callback($lastKey, $array[$lastKey]);

        return $array[$lastKey];
    }

    /**
     * Return the first element in an array passing a given truth test.
     *
     * @template TKey
     * @template TValue
     * @template TFirstDefault
     *
     * @param  iterable<TKey, TValue>  $array
     * @param  (callable(TValue, TKey): bool)|null  $callback
     * @param  TFirstDefault|(\Closure(): TFirstDefault)  $default
     * @return TValue|TFirstDefault
     */
    public static function first(iterable $array, callable $callback = null, $default = null)
    {
        foreach ($array as $key => $value)
            if (is_callable($callback) && $callback($value, $key))
                return $value;

        return Data::valueOf($default);
    }

    /**
     * Determine whether the given value is array accessible.
     *
     * @param  mixed  $value
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }

    /**
     * Determine if the given key exists in the provided array.
     *
     * @param  iterable  $array
     * @param  string|int  $key
     * @return bool
     */
    public static function exists(iterable $array, string|int $key)
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        if (is_float($key)) {
            $key = (string) $key;
        }

        return array_key_exists($key, $array);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  \ArrayAccess|array  $array
     * @param  string|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (!static::accessible($array)) {
            return Data::valueOf($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $array[$key] ?? Data::valueOf($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (static::accessible($array) && static::exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return Data::valueOf($default);
            }
        }

        return $array;
    }

    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * @param  mixed  $value
     * @return iterable
     */
    public static function wrap($value): iterable
    {
        if (is_null($value))
            return [];

        return is_iterable($value) ? $value : [$value];
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param  iterable  $array
     * @param  iterable|string  $keys
     * @return iterable
     */
    public static function only(iterable $array, iterable $keys): iterable
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  iterable  $array
     * @param  iterable|string|int|float  $keys
     * @return void
     */
    public static function forget(iterable &$array, iterable|string|int|float $keys): void
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // if the exact key exists in the top-level, remove it
            if (static::exists($array, $key)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // clean up before each pass
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && static::accessible($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param  iterable  $array
     * @param  iterable|string|int|float  $keys
     * @return iterable
     */
    public static function except(iterable $array, iterable|string|int|float $keys): iterable
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Pluck an array of values from an array.
     *
     * @param  iterable  $array
     * @param  string|iterable|int|null  $value
     * @param  string|iterable|null  $key
     * @return array
     */
    public static function pluck(iterable $array, string|iterable|int|null $value, string|iterable|null $key = null)
    {
        $results = [];

        [$value, $key] = static::explodePluckParameters($value, $key);

        foreach ($array as $item) {
            $itemValue = self::data_get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = self::data_get($item, $key);

                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Collapse an array of arrays into a single array.
     *
     * @param  iterable  $array
     * @return array
     */
    public static function collapse(iterable $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if ($values instanceof Collection) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

    /**
     * Get an item from an array or object using "dot" notation.
     *
     * @param  mixed  $target
     * @param  string|iterable|int|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function data_get($target, string|iterable|int|null $key, $default = null)
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $i => $segment) {
            unset($key[$i]);

            if (is_null($segment)) {
                return $target;
            }

            if ($segment === '*') {
                if ($target instanceof Collection) {
                    $target = $target->all();
                } elseif (!is_iterable($target)) {
                    return Data::valueOf($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = self::data_get($item, $key);
                }

                return in_array('*', $key) ? Arr::collapse($result) : $result;
            }

            $segment = match ($segment) {
                '\*' => '*',
                '\{first}' => '{first}',
                '{first}' => array_key_first($target),
                '\{last}' => '{last}',
                '{last}' => array_key_last($target),
                default => $segment,
            };

            if (Arr::accessible($target) && Arr::exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return Data::valueOf($default);
            }
        }

        return $target;
    }

    /**
     * Run a map over each of the items in the array.
     *
     * @param  array  $array
     * @param  callable  $callback
     * @return array
     */
    public static function map(iterable $array, callable $callback): array
    {
        // TODO:
        // if($array instanceof Collection)
        //     $array->map();

        $keys = array_keys($array);

        try {
            $items = array_map($callback, $array, $keys);
        } catch (ArgumentCountError) {
            $items = array_map($callback, $array);
        }

        return array_combine($keys, $items);
    }

    /**
     * Explode the "value" and "key" arguments passed to "pluck".
     *
     * @param  string|iterable  $value
     * @param  string|iterable|null  $key
     * @return array
     */
    protected static function explodePluckParameters(string|iterable $value, string|iterable|null $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_iterable($key) ? $key : explode('.', $key);

        return [$value, $key];
    }

    /**
     * Run an associative map over each of the items.
     *
     * The callback should return an associative array with a single key/value pair.
     *
     * @template TKey
     * @template TValue
     * @template TMapWithKeysKey of array-key
     * @template TMapWithKeysValue
     *
     * @param  array<TKey, TValue>  $array
     * @param  callable(TValue, TKey): array<TMapWithKeysKey, TMapWithKeysValue>  $callback
     * @return array
     */
    public static function mapWithKeys(iterable $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }

    /**
     * Conditionally compile classes from an array into a CSS class list.
     *
     * @param  iterable  $array
     * @return string
     */
    public static function toCssClasses(iterable $array): string
    {
        $classList = static::wrap($array);

        $classes = [];

        foreach ($classList as $class => $constraint) {
            if (is_numeric($class)) {
                $classes[] = $constraint;
            } elseif ($constraint) {
                $classes[] = $class;
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Conditionally compile styles from an array into a style list.
     *
     * @param  iterable  $array
     * @return string
     */
    public static function toCssStyles(iterable $array): string
    {
        $styleList = static::wrap($array);

        $styles = [];

        foreach ($styleList as $class => $constraint) {
            if (is_numeric($class)) {
                $styles[] = Str::finish($constraint, ';');
            } elseif ($constraint) {
                $styles[] = Str::finish($class, ';');
            }
        }

        return implode(' ', $styles);
    }

    /**
     * Checks if the array is associative
     *
     * @param iterable $array
     * @return bool
     */
    public static function isAssociative(iterable $array): bool
    {
        if ($array instanceof Collection)
            return $array->isAssociative();

        return array_keys($array) !== range(0, count($array) - 1);
    }
}