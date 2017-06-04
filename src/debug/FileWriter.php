<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-03
 */

namespace fk\helpers\debug;

use fk\helpers\Dumper;

class FileWriter implements WriterInterface
{

    /**
     * @var resource
     */
    protected $handler;

    protected $filename;

    protected $allowedLogSize = 1024 * 1024 * 5;

    protected $mode;

    protected $offset = 0;

    /**
     * @var array
     */
    protected $startFields;

    public function __construct($filename, $startFields = [])
    {
        $this->filename = $filename;
        $this->sizeControl();
        $this->handler = fopen($this->filename, 'a');
        $this->startFields = $startFields;
    }

    protected function sizeControl()
    {
        if (!is_file($this->filename) || filesize($this->filename) < $this->allowedLogSize) return;

        $index = 1;
        while (file_exists("$this->filename.$index")) {
            $index++;
        }
        $newFilename = $this->filename . '.' . $index;

        rename($this->filename, $newFilename);
        file_put_contents($this->filename, '');
    }

    public function write(array $something)
    {
        list ($title, $data) = $something;
        $date = date('H:i:s');
        $log = "\n[$date] $title\n" . Dumper::dump($data) . "\n";
        $this->fwrite($log);
    }

    public function end()
    {
        $this->fwrite("\n\n\n");
        fclose($this->handler);
    }

    protected function fwrite(string $string)
    {
        fwrite($this->handler, $string);
    }

    public function start()
    {
        $this->fwrite($this->getStartWith($this->startFields));
    }

    protected function getStartWith(array $fields): string
    {
        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        $log = <<<DEL
 -----------------------------------------------------------
|
|   Welcome
|   Date    : $date
|   Client  : $ip
|   Method  : {$_SERVER['REQUEST_METHOD']}

DEL;
        if (is_array($fields) && $fields) {
            $log .= <<<LOG
|
|===========================================================
|   <OTHERS>
|===========================================================
|

LOG;
        }
        foreach ($fields as $k => $v) {
            if (!is_scalar($k)) continue;
            $log .= <<<LOG
|   $k  : $v

LOG;
        }
        $log .= <<<DEL
|
 -----------------------------------------------------------

DEL;
        return $log;
    }
}