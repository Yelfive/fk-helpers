<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-10
 */

namespace fk\helpers;

class ArrayHelper
{
    /**
     * Return data with keys only listed in `$keys`
     * @param array $data
     * @param array $keys
     * @return array
     */
    public static function only(array $data, array $keys)
    {
        $only = [];
        foreach ($keys as $key) {
            $only[$key] = $data[$key] ?? null;
        }
        return $only;
    }

    /**
     * Get value from key with dot syntax
     * @param array $data
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public static function get(array $data, string $key, $defaultValue = null)
    {
        if (array_key_exists($key, $data)) return $data[$key];

        foreach (explode('.', $key) as $part) {
            if (array_key_exists($part, $data)) {
                $data = $data[$part];
            } else {
                return $defaultValue;
            }
        }
        return $data;
    }
}