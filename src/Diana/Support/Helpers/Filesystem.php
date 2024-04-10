<?php

namespace Diana\Support\Helpers;

use Diana\Exceptions\FileNotFoundException;
use Diana\Runtime\Application;

class Filesystem
{
    // CLEAN

    protected static string $basePath;

    public static function setBasePath(string $basePath)
    {
        self::$basePath = $basePath;
    }

    public static function absPath(array|string $segments = ["."], string $delimiter = "/")
    {
        if (is_string($segments))
            $segments = explode($delimiter, $segments);

        if (isset($segments[0]) && $segments[0] == ".")
            $segments[0] = self::$basePath ?? "";

        return join(DIRECTORY_SEPARATOR, $segments);
    }



    // TODO: UNCLEAN


    /**
     * Get the returned value of a file.
     *
     * @param  string  $path
     * @param  array  $data
     * @return mixed
     *
     * @throws FileNotFoundException
     */
    public static function getRequire($path, array $data = [])
    {
        if (is_file($path)) {
            $__path = $path;
            $__data = $data;

            return (static function () use ($__path, $__data) {
                extract($__data, EXTR_SKIP);

                return require $__path;
            })();
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }

    /**
     * Get the contents of a file.
     *
     * @param  string  $path
     * @param  bool  $lock
     * @return string
     *
     * @throws FileNotFoundException
     */
    public static function get($path, $lock = false)
    {
        if (is_file($path)) {
            return $lock ? self::sharedGet($path) : file_get_contents($path);
        }

        throw new FileNotFoundException("File does not exist at path {$path}.");
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string  $path
     * @return string
     */
    public static function sharedGet($path)
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, filesize($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }
}