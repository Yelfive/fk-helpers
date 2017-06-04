<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-03
 */

namespace fk\helpers\debug;

interface WriterInterface
{
    public function start();

    /**
     * @param array $something [string $title, mixed $data]
     * @see Capture::TITLE_REQUEST
     * @see Capture::TITLE_RESPONSE
     * @see Capture::$logVars
     */
    public function write(array $something);

    public function end();
}