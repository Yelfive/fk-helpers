<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-29
 */

namespace fk\helpers;

class DebugRequestCapture
{
    public $logFilename;

    public $request = [];

    public function __construct($logFilename)
    {
        $this->logFilename = $logFilename;
    }

    public function capture()
    {
        $request = [
            'Get: ' => $_GET,
            'Post: ' => $_POST,
            'Headers: ' => $this->collectHeaders(),
            'Files: ' => $_FILES,
        ];


        $this->request = array_filter($request, function ($v) {
            return !empty($v);
        });

        $date = date('Y-m-d H:i:s');
        file_put_contents($this->logFilename, "\n[$date] " . Helper::dump($this->request), FILE_APPEND);
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
        return $header;
    }
}