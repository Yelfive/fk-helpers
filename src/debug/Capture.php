<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-29
 */

namespace fk\helpers\debug;

use fk\helpers\SingletonTrait;

/**
 * Capture for the user interfaces
 *
 * Writer for two things: record and write
 *
 *  - write
 *          An interface to cache data
 *  - persist
 *          An interface to save data permanently
 *
 * @method static static singleton(WriterInterface $writer = null, bool $debug = true) Parameter 1 is optional only when its initialized already, otherwise null will be returned
 */
class Capture
{
    use SingletonTrait;

    /**
     * Whether application is in debug mode
     * Capture only works when `debug=true`
     */
    protected $debug;

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @var array Variables to be saved when requested
     */
    protected $requestLogVars = ['request_header', 'query', 'form', 'session', 'file'];

    /**
     * @var array Variables to be saved when terminating, after response sent
     */
    protected $responseLogVars = ['response_header', 'response_body', 'response_session'];

    /**
     * @var string Output buffer, to store ob for persistence
     * @see shutdown
     * @see prepareResponseBody
     */
    protected $outputBuffer;

    /**
     * Capture constructor.
     * @param WriterInterface $writer
     * @param bool $debug False to disable capture
     */
    public function __construct(WriterInterface $writer, bool $debug = true)
    {
        if (false === $this->debug = $debug) return;

        $this->debug = $debug;
        $this->writer = $writer;
        ob_start(function ($buffer) {
            return $this->outputBuffer = $buffer;
        });
        $this->capture($this->requestLogVars);
        register_shutdown_function(function () {
            $this->shutdown();
        });
    }

    protected function shutdown()
    {
        // finish request will clear all output buffer
        if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();

        $this->capture($this->responseLogVars);
        $this->writer->persist();
    }

    protected function capture($logVars)
    {
        $request = [];
        foreach ($logVars as $var) {
            if (!isset($request[$var])) {
                $method = "prepare" . str_replace('_', '', ucwords($var, '_'));
                if (method_exists($this, $method)) {
                    $request[$var] = $this->$method();
                } else if (isset($GLOBALS[$var])) {
                    $request[$var] = $GLOBALS[$var];
                }
            }
        }

        // Capture only the non-empty elements
        $request = array_filter($request, function ($v) {
            return !empty($v);
        });

        $this->write($request);
    }

    protected function prepareResponseBody()
    {
        return $this->outputBuffer;
    }

    protected function prepareResponseSession()
    {
        return $_SESSION ?? [];
    }

    protected function prepareRequestHeader()
    {
        $headers = [];
        foreach ($_SERVER as $k => $v) {
            if (strncmp($k, 'HTTP_', 5) === 0) {
                $k = substr(strtolower($k), 5);
                $k = str_replace('_', '-', ucwords($k, '_'));
                $headers[$k] = $v;
            }
        }
        return $headers;
    }

    protected function prepareResponseHeader()
    {
        $headers = [];
        foreach (headers_list() as $v) {
            if (false !== $pos = strpos($v, ':')) {
                $headers[substr($v, 0, $pos)] = substr($v, $pos + 1);
            }
        }
        if ($headers) $this->write(['response_header' => $headers]);
    }

    protected function prepareQuery()
    {
        return $_GET;
    }

    protected function prepareForm()
    {
        return $_POST;
    }

    protected function prepareFile()
    {
        return $_FILES;
    }

    protected function prepareSession()
    {
        return $_SESSION ?? [];
    }

    /**
     * Record data for persisting purpose
     * @param array $data
     */
    public function write(array $data)
    {
        if (!$this->debug || !$this->writer) return;

        $this->writer->write($data);
    }
}

