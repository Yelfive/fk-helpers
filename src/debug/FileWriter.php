<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-03
 */

namespace fk\helpers\debug;

use fk\helpers\Dumper;

/**
 * **Usage**
 *
 * ```
 * $writer = new \fk\helpers\debug\FileWriter($filename);
 * $capture = new Capture($writer, $debug)
 * ```
 */
class FileWriter implements WriterInterface
{
    public $filename;

    protected $allowedLogSize = 1024 * 1024 * 5;

    protected $mode;

    protected $offset = 0;

    protected $log = [];

    /**
     * @var array
     */
    protected $startingFields;

    /**
     * FileWriter constructor.
     * This writer does not consider the concurrency, and should be used only in development environment
     * @param string $filename To log file to write in
     * @param array $startingFields The extra fields to be written at the start of current capture session
     */
    public function __construct($filename, $startingFields = [])
    {
        $this->filename = $filename;
        $this->sizeControl();
        $this->startingFields = $startingFields;

        $this->start();
    }

    /**
     * Check the size of the log file, if it is larger than [[allowedLogSize]],
     * it will be renamed to file with a index appended.
     */
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

    /**
     * Interface to
     * @param array $something
     */
    public function write(array $something)
    {
        $date = date('H:i:s');
        $log = '';
        foreach ($something as $title => $data) {
            $title = str_replace('_', ' ', ucwords($title, '_'));
            $log .= "\n[$date] $title\n    " . Dumper::dump($data, false, 2) . "\n";
        }
        $this->log($log);
    }

    /**
     * Record a log string
     * @param string $string
     * @return $this
     */
    protected function log(string $string)
    {
        $this->log[] = $string;
        return $this;
    }

    protected function start()
    {
        $this->log($this->getStartWith($this->startingFields));
    }

    protected function getStartWith(array $fields): string
    {
        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        $method = PHP_SAPI === 'cli' ? 'cli' : ($_SERVER['REQUEST_METHOD'] ?? 'unknown');
        $other = $this->prepareFields($fields);

        return <<<EOF
+-----------------------------------------------------------
|                       Welcome
+-----------------------------------------------------------
|   Date    : {$date}
|   Client  : {$ip}
|   Method  : {$method}{$other}
+-----------------------------------------------------------

EOF;
    }

    protected function prepareFields($fields)
    {

        $log = '';
        if ($fields && is_array($fields)) {
            $log .= <<<EOF
|
|===========================================================
|   <OTHERS>
|===========================================================

EOF;
            foreach ($fields as $k => $v) {
                if (!is_scalar($k)) continue;
                $k = str_replace('_', ' ', ucwords($k, '_'));
                $log .= "|   $k  : $v\n";
            }
            $log = rtrim($log, "\n");
        }
        return $log;
    }

    /**
     * This will be triggered when script ends, to save request permanently
     * @see Capture::__construct
     * @see Capture::shutdown
     */
    public function persist()
    {
        $this->log[] = "\n\n";
        $handler = fopen($this->filename, 'a');
        foreach ($this->log as $item) {
            fwrite($handler, "$item\n");
        }
        fclose($handler);
    }
}