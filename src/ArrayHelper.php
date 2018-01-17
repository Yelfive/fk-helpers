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

    /**
     * `$data` must have only one child,
     * of which the key is the root element node name
     * and the value the descendants
     * @param array $data
     * @param string $version
     * @param string $encoding
     * @return string
     * @throws \Exception
     */
    public static function toXML(array $data, string $version = '1.0', $encoding = 'UTF-8'): string
    {
        if (count($data) !== 1) throw new \Exception('Parameter 1 should contain a root name');

        $xml = "<?xml version=\"$version\" encoding=\"$encoding\"?>";
        $xml .= static::iterateXMLElements($data);
        return $xml;
    }

    /**
     * **Tag**
     *
     * Tag names cannot contain any of the characters
     * ```
     * !"#$%&'()*+,/;<=>?@[\]^`{|}~
     * ```
     * nor a space character,
     *
     * and cannot begin with "-", ".", or a numeric digit.
     * @param array $elements
     * @return string
     * @throws \Exception
     * @link https://en.wikipedia.org/wiki/XML
     */
    protected static function iterateXMLElements(array $elements)
    {
        $xml = '';
        $invalid = '!"#$%&\'()*+,/;<=>?@[\]^`{|}~ ';
        foreach ($elements as $tag => $v) {
            for ($i = 0; $i < strlen($invalid); $i++) if (false !== strpos($tag, $invalid[$i])) throw new \Exception('Tag name cannot contain any of `!"#$%&\'()*+,/;<=>?@[\]^`{|}~`: ' . $tag);

            if (preg_match('/^[\d\.\-]/', $tag)) throw new \Exception('Tag name cannot begin with "-", ".", or a numeric digit: ' . $tag);

            if (is_array($v)) {
                $xml .= "<$tag>" . static::iterateXMLElements($v) . "</$tag>";
            } else {
                if (false !== strpos($v, '<')) {
                    $fragments = explode(']]>', $v);
                    $value = '<![CDATA[' . implode(']]]]><![CDATA[>', $fragments) . ']]>';
                } else {
                    $value = $v;
                }

                $xml .= "<$tag>$value</$tag>";
            }
        }
        return $xml;
    }

    /**
     * If the number is 1, an item of `$data` will be returned,
     * otherwise an array of items in `$data` will be returned
     * @param array $data
     * @param int $number
     * @return mixed
     */
    public static function random(array $data, $number = 1)
    {
        if ($number == 1) {
            return $data[array_rand($data, 1)];
        } else if ($number > 1) {
            $keys = array_rand($data, $number);
            $result = [];
            foreach ($keys as $key) $result[] = $data[$key];

            return $result;
        } else {
            return null;
        }
    }

}