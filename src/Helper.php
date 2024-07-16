<?php

namespace Leoboy\Desensitization;

final class Helper
{
    /**
     * Get an item from an array using "dot" notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Arr.php#L246
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @param  string  $delimiter
     * @return mixed
     */
    public static function arrayGet(array $array, $key, $default = null, $delimiter = '.')
    {
        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode($delimiter, $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Arr.php#L81
     */
    public static function arrayDot(array $array, string $glue = '.', string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, self::arrayDot($value, $glue, $prepend.$key.$glue));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Set an item on an array or object using dot notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/helpers.php#L437
     *
     * @param  mixed  $target
     * @param  string|array|null  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @param  string  $divider
     * @param  string  $wildcardChar
     */
    public static function arraySet(
        &$target,
        $key,
        $value,
        $overwrite = true,
        $divider = '.',
        $wildcardChar = '*'
    ): array {
        if (is_null($key)) {
            if ($overwrite) {
                return $target = array_merge($target, $value);
            }

            return $target = array_merge($value, $target);
        }

        $segments = is_array($key) ? $key : explode($divider, $key);

        if (($segment = array_shift($segments)) === $wildcardChar) {
            if (! is_array($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    self::arraySet($inner, $segments, $value, $overwrite, $divider, $wildcardChar);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (is_array($target)) {
            if ($segments) {
                if (! array_key_exists($segment, $target)) {
                    $target[$segment] = [];
                }

                self::arraySet($target[$segment], $segments, $value, $overwrite, $divider, $wildcardChar);
            } elseif ($overwrite || ! array_key_exists($segment, $target)) {
                $target[$segment] = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                self::arraySet($target[$segment], $segments, $value, $overwrite, $divider, $wildcardChar);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}
