<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-03
 */

namespace fk\helpers\debug;

class FileWriter implements WriterInterface
{

    protected $handler;

    protected $filename;

    protected $allowedLogSize = 1024 * 1024 * 5;

    protected $mode;

    protected $offset = 0;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->sizeControl();
        $this->handler = fopen($this->filename, 'a');
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

    public function write($something)
    {
        fwrite($this->handler, $something);
    }

    public function close()
    {
        fwrite($this->handler, "\n\n\n");
        fclose($this->handler);
    }

}