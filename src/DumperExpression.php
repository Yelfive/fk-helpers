<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-24
 */

namespace fk\helpers;

class DumperExpression
{
    public $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }
}