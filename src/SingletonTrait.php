<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-30
 */

namespace fk\helpers;

trait SingletonTrait
{
    protected static $_instance;

    /**
     * @param array ...$args
     * @return static
     */
    public static function singleton(...$args)
    {
        if (!static::$_instance) static::$_instance = new static(...$args);
        return static::$_instance;
    }
}