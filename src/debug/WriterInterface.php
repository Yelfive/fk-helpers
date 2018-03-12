<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-03
 */

namespace fk\helpers\debug;

interface WriterInterface
{
    /**
     * Interface to allow [[Capture]] to record things
     * @param array $something Something to write with , it must be in the form of `[string $title => mixed $data]`
     * @see Capture::$requestLogVars
     */
    public function write(array $something);

    /**
     * This will be triggered when script ends, to save request permanently
     * @see Capture::__construct
     * @see Capture::shutdown
     */
    public function persist();
}