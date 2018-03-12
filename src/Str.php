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
     * @param string $input
     * @return string
     */
    public static function purify($input)
    {
        return preg_replace('/\s/', '', $input);
    }

    /**
     * Change from snake case(`hello_world`) into cammel case (`helloWorld)
     * @param string $input
     * @return string
     */
    public static function toCammelCase($input)
    {
        return preg_replace_callback('/_[a-zA-Z]/', function ($v) {
            return ucfirst(substr($v[0], 1));
        }, $input);
    }
}