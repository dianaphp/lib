<?php

namespace Diana\Support\Helpers;

use Diana\Support\Exceptions\FileNotFoundException;

class Filesystem
{
    /**
     * Get the returned value of a file.
     *
     * @param string $path
     * @param array  $data
     * @return mixed
     *
     * @throws FileNotFoundException
     */
    public static function getRequire(string $path, array $data = []): mixed
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

    // TODO: UNCLEAN

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