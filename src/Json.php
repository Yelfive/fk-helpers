<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-21
 */

namespace fk\helpers;

class Json
{
    public const TAB_LENGTH_DEFAULT = 4;

    public static function merge(...$arrays): array
    {
        $merged = array_shift($arrays);
        foreach ($arrays as $array) {
            $merged = static::mergeTwo($merged, $array);
        }
        return $merged;
    }

    protected static function mergeTwo(array $first, array $second): array
    {
        $merged = $first;
        $indexed = static::isIndex($first) && static::isIndex($second);
        foreach ($second as $k => $v) {
            if (is_array($v)) {
                $origin = $merged[$k] ?? [];
                if (!is_array($origin)) $origin = [];

                $merged[$k] = static::mergeTwo($origin, $v);
            } else if ($indexed) {
                $merged[] = $v;
            } else {
                $merged[$k] = $v;
            }
        }
        return $merged;
    }

    protected static function isIndex(array $array): bool
    {
        $index = 0;
        foreach ($array as $k => $v) {
            if ($k !== $index++) return false;
        }
        return true;
    }

    public static function dump(array $array, int $indent = self::TAB_LENGTH_DEFAULT): string
    {
        static $tabLength;
        if (!$tabLength) $tabLength = $indent;

        $space = str_repeat(' ', $indent);
        $braceSpace = substr($space, $tabLength);
        $isIndex = static::isIndex($array);
        $json = $isIndex ? '[' : '{';
        foreach ($array as $k => $v) {
            $json .= "\n$space";
            $json .= $isIndex ? '' : (static::escape($k) . ': ');
            $json .= is_array($v) ? static::dump($v, $indent + $tabLength) : static::escape($v);
            $json .= ',';
        }
        $json = rtrim($json, ',');
        $json .= "\n$braceSpace";
        $json .= $isIndex ? ']' : '}';
        return $json;
    }

    protected static function escape($value)
    {
        if (is_float($value) || is_int($value)) {
            return $value;
        } else if (is_null($value)) {
            return 'null';
        } else if (is_bool($value)) {
            return $value === true ? 'true' : 'false';
        } else if (is_string($value)) {
            return '"' . addcslashes($value, '"\\') . '"';
        } else {
            return '';
        }
    }
}