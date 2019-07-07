<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2018-01-16
 */

namespace fk\helpers\mock\mocker;

use fk\helpers\ArrayHelper;

class Str
{

    /**
     * Return random number of alphabet characters
     *
     * ```php
     * // random from 0 to 20, as default
     * randomAlphabet()
     * // random exact `$length`
     * randomAlphabet($length)
     * // random from $min to $max
     * randomAlphabet($min, $max)
     * ```
     *
     * @param int $min Minimum number of alphabet characters to return
     * @param int $max Maximum number of alphabet characters to return
     * @return string
     */
    public static function randomAlphabet(int $min = null, int $max = null)
    {
        if ($min === $max && $min === null) {
            $min = 0;
            $max = 20;
        } else if ($max === null) {
            $max = $min;
        }

        if ($min < 0) $min = 0;
        if ($max < $min) $max = $min;

        $len = rand($min, $max);
        $ascii_ranges = [[65, 90], [97, 122]];

        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $ord = call_user_func_array('rand', $ascii_ranges[array_rand($ascii_ranges, 1)]);
            $str .= chr($ord);
        }
        return $str;
    }

    /**
     * Return random number of chinese characters
     *
     * ```php
     * // random from 0 to 20, as default
     * randomChinese()
     * // random exact `$length`
     * randomChinese($length)
     * // random from $min to $max
     * randomChinese($min, $max)
     * ```
     *
     * @param int $min Minimum number of chinese characters to return
     * @param int $max Maximum number of chinese characters to return
     * @return string
     */
    public static function randomChinese(int $min = null, int $max = null)
    {
        if ($min === null) {
            $min = 0;
            $max = 20;
        } else if ($max === null) {
            $max = $min;
        }

        if ($min < 0) $min = 0;
        if ($max < $min) $max = $min;

        $len = rand($min, $max);
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            $str .= iconv('utf-16', 'utf-8', chr(rand(0x00, 0xFF)) . chr(rand(0x4E, 0x99)));
        }
        return $str;
    }

    /**
     * Random arbitrary number of words between `$min` and `$max`
     *
     * If `$min=1` or `$min=$max=1` passed, string will be returned, otherwise, array
     *
     * ```php
     * // Random 1 word, string will be returned
     * randomWord(1)
     * // Returns a array of 1 word or two
     * randomWord(1, 2)
     * ```
     *
     * @param int $min
     * @param int $max
     * @return array|string
     */
    public static function randomWord(int $min = null, int $max = null)
    {
        static::normalizeLimitation($min, $max);
        $dict = explode("\n", file_get_contents(__DIR__ . '/../resource/dict.txt'));
        $result = ArrayHelper::random($dict, rand($min, $max));
        if ($min === $max && $min === 1) return $result;
        return (array)$result;
    }

    /**
     * Returns an URL with 1-3 leveled-domain, not take top-level into account
     * @return string
     */
    public static function randomUrl()
    {
        $scheme = 'http' . (rand(0, 1) ? '' : 's');
        $top = explode("\n", file_get_contents(__DIR__ . '/../resource/top-level-domain-name.txt'));


        return "$scheme://" . implode('.', static::randomWord(1, 3)) . '.' . ArrayHelper::random($top, 1);
    }

    protected static function normalizeLimitation(&$min, &$max)
    {
        if ($min === null) {
            $min = 0;
            $max = 20;
        } else if ($max === null) {
            $max = $min;
        }

        if ($min < 0) $min = 0;
        if ($max < $min) $max = $min;
    }
}