<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-03
 */

namespace fk\helpers\debug;

interface WriterInterface
{
    public function write($something);
}