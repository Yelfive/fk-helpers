<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-30
 */

namespace fk\helpers;

trait BuildTrait
{
    protected static $instance;

    /**
     * @param array ...$args
     * @return static
     */
    public static function build(...$args)
    {
        if (!static::$instance) static::$instance = new static(...$args);
        return static::$instance;
    }
}