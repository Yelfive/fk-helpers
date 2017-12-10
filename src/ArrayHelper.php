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
}