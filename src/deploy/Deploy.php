<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-29
 */

namespace fk\helpers\deploy;

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
defined('STDERR') or define('STDERR', fopen('php://stderr', 'w'));

class Deploy
{
    public static function listen()
    {
        $instance = new static();
        $instance->confirm('Are you sure?');
    }

    public function confirm($message)
    {
        fwrite(STDOUT, $message . ' [y/n] ');
        return in_array(strtolower(fgetc(STDIN)), ['y', 'yes']);
    }

    protected function stdin($length = 1)
    {
//        return
    }
}