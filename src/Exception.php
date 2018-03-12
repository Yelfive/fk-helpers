<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-30
 */

namespace fk\helpers;

use Throwable;

class Exception extends \Exception
{
    public const TYPE_NORMAL = 0B1;
    public const TYPE_RESULT = 0B10;

    protected $type;

    public function __construct($message = "", $code = 0, $type = self::TYPE_NORMAL, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getType()
    {
        return $this->type;
    }
}