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
     * Change from snake case(`hello_world`) into camel case (`helloWorld`)
     * @param string $input snake_cased string
     * @return string CamelCased String
     * @deprecated Typo
     */
    public static function toCammelCase($input)
    {
        return preg_replace_callback('/_[a-zA-Z]/', function ($v) {
            return ucfirst(substr($v[0], 1));
        }, $input);
    }

    /**
     * Change from snake case(`hello_world`) into camel case (`helloWorld`)
     * @param string $input snake_cased string
     * @return string CamelCased String
     */
    public static function toCamelCase($input)
    {
        return preg_replace_callback('/_[a-zA-Z]/', function ($v) {
            return ucfirst(substr($v[0], 1));
        }, $input);
    }

    /**
     * @param string $input CamelCased string
     * @return string snake_cased string
     */
    public static function toSnakeCase(string $input)
    {
        return preg_replace_callback('#[A-Z]#', function ($v) {
            return '_' . strtolower($v[0]);
        }, lcfirst($input));
    }
}