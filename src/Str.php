<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2018-01-20
 */

namespace fk\helpers;

class Str
{
    /**
     * Clear any invisible character
     * @param $input
     * @return mixed
     */
    public static function purify($input)
    {
        return preg_replace('/\s/', '', $input);
    }
}