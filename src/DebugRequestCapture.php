<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-29
 */

namespace fk\helpers;

class DebugRequestCapture
{
    protected $logFilename;

    protected $startWith;

    /**
     * Whether application is in debug mode
     * Capture only works when `debug=true`
     */
    protected $debug;

    /**
     * @var static
     */
    protected static $instance;

    public function __construct(string $logFilename, bool $debug = true, array $startFields = [])
    {
        $this->logFilename = $logFilename;

        $this->debug = $debug;

        static::$instance = $this;

        $this->startWith = $this->getStartWith($startFields);
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
            if (!is_scalar($k) || !is_scalar($v)) continue;
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

    public function capture(callable $callback)
    {
        if ($this->debug) {
            $this->sizeControl();
            $this->writeStart();
            $request = [
                'Get: ' => $_GET,
                'Post: ' => $_POST,
                'Headers: ' => $this->collectHeaders(),
                'Files: ' => $_FILES,
            ];

            $request = array_filter($request, function ($v) {
                return !empty($v);
            });

            $this->write('Request', $request);
        }

        if ($this->debug) {
            ob_start();
        }

        $content = call_user_func($callback);

        if ($this->debug) {
            $content = ob_get_clean() . $content;
        }

        if ($this->debug && $content !== null) {
            $this->write('Response', $content);
        }
    }

    /**
     * Add capture log
     * @param string $title
     * @param mixed $data
     */
    public static function add(string $title, $data)
    {
        if (!static::$instance) return;

        static::$instance->write($title, $data);
    }

    protected function writeStart()
    {
        file_put_contents($this->logFilename, $this->startWith, FILE_APPEND);
    }

    protected function write($title, $data)
    {
        $date = date('Y-m-d H:i:s');
        $log = "\n[$date] $title\n" . Helper::dump($data) . "\n";
        file_put_contents($this->logFilename, $log, FILE_APPEND);
    }

    protected function collectHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (strncmp($k, 'HTTP_', 5) === 0) {
                $k = substr(strtolower($k), 5);
                $k = str_replace('_', '', ucwords($k, '_'));
                $headers[$k] = $v;
            }
        }
        return $headers;
    }

    protected function sizeControl()
    {
        if (!is_file($this->logFilename) || filesize($this->logFilename) < 1024 * 1024 * 5) return;

        $index = 1;
        while (file_exists("$this->logFilename.$index")) {
            $index++;
        }
        $newFilename = $this->logFilename . '.' . $index;

        rename($this->logFilename, $newFilename);
        file_put_contents($this->logFilename, '');
    }
}