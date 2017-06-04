<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-03
 */

namespace fk\helpers\debug;

interface WriterInterface
{
    /**
     * @param array $something [string $title, mixed $data]
     * @see Capture::$logVars
     */
    public function write(array $something);

}