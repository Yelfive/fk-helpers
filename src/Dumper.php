<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-01
 */

namespace fk\helpers;

class Dumper
{

    public static function isIndexedArray($array)
    {
        $i = -1;
        foreach ($array as $k => $v) {
            if (is_string($k) || $k !== $i + 1) {
                return false;
            } else {
                $i = $k;
            }
        }
        return true;
    }

    /**
     * @param mixed $input
     * @param bool $inline
     * @param int $depth
     * @return string
     * @throws \Exception
     */
    public static function dump($input, bool $inline = false, $depth = 1): string
    {
        if (is_scalar($input) || is_null($input)) {
            return static::wrapScalar($input);
        } else if (is_array($input)) {
            if (!$input) return '[]';
            $string = '';

            $space = $inline ? ' ' : "\n" . str_repeat(' ', $depth * 4);

            if (static::isIndexedArray($input)) {
                foreach ($input as $value) {
                    $string .= ",$space" . (is_scalar($value) ? static::wrapScalar($value) : static::dump($value, $inline, $depth + 1));
                }
            } else {
                foreach ($input as $k => $value) {
                    $string .= ",$space" . static::wrapScalar($k) . ' => ' . (is_scalar($value) ? static::wrapScalar($value) : static::dump($value, $inline, $depth + 1));
                }
            }

            $chars = $inline ? ', ' : ',';
            return '[' . ltrim($string, $chars) . ($inline ? '' : ',') . substr($space, 0, -4) . ']';
        } else if (is_object($input)) {
            return '<object ' . get_class($input) . '>';
        }
        throw new \Exception('Cannot dump such data type: ' . var_export($input, true));
    }

    public static function wrapScalar($input)
    {
        if (is_numeric($input)) {
            return $input;
        } else if (is_string($input)) {
            return "'$input'";
        } else if (is_null($input)) {
            return 'null';
        } else if (is_bool($input)) {
            return $input ? 'true' : 'false';
        } else if (is_int($input)) {
            return $input;
        }
    }

    public static function asJson($data, $tab_length = 0)
    {
    }
}