<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-29
 */

namespace fk\helpers;

class DebugRequestCapture
{
    public $logFilename;

    /**
     * Whether application is in debug mode
     * Capture only works when `debug=true`
     */
    public $debug;

    public function __construct($logFilename, $debug = true)
    {
        $this->logFilename = $logFilename;

        $this->debug = $debug;
    }

    public function capture(callable $callback)
    {
        if ($this->debug) {
            $this->write(null, null);
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

    protected function write($title, $data)
    {
        $date = date('Y-m-d H:i:s');
        if ($title === null) {
            $ip = $_SERVER['HTTP_X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
            $log = <<<DEL

 -----------------------------------------------------------
|
|   Welcome, 
|   Date: $date
|   IP  : $ip
|
 -----------------------------------------------------------


DEL;
        } else {
            $log = "\n[$date] $title\n" . Helper::dump($data) . "\n";
        }
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
}