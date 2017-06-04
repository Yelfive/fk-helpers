<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-29
 */

namespace fk\helpers\debug;

use fk\helpers\Dumper;

/**
 * Class DebugRequestCapture
 * @package fk\helpers
 * @method static null add(string $title, mixed $data)
 *
 * @method $this header(array $headers)
 * @method $this query(array $queryParams)
 * @method $this form(array $formData)
 * @method $this session(array $session)
 * @method $this file(array $files)
 * @method $this cookie(array $cookies)
 */
class Capture
{

    const TITLE_REQUEST = 'Request';
    const TITLE_RESPONSE = 'Response';

    /**
     * @var string
     */
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

    /**
     * @var WriterInterface
     */
    protected $writer;

    /**
     * @var array
     */
    protected $request = [];

    protected $logVars = ['header', 'query', 'form', 'session', 'file', 'cookie'];

    public function __construct($writer, bool $debug = true, array $startFields = [])
    {
        if (!$debug) return;

        $this->debug = $debug;
        static::$instance = $this;
        $this->writer = $writer instanceof WriterInterface ? $writer : new $writer;
    }

    public function overwriteRequest($request)
    {
        $this->request = $request;
    }

    public function __call($name, $arguments)
    {
        if (in_array($name, $this->logVars)) {
            $value = $arguments[0];
            $this->request[$name] = is_callable($value) ? $this->call($value) : $value;
            return $this;
        }
        throw new \Exception("Calling undefined method $name of " . __CLASS__);
    }

    protected function call(callable $callback)
    {
        return call_user_func($callback);
    }

    public function capture(callable $callback)
    {
        if (!$this->debug) return $this->call($callback);

        $this->start();

        $this->captureRequest();

        if (null !== $content = $this->call($callback)) $this->write(self::TITLE_RESPONSE, $content);

        $this->end();
        return true;
    }

    protected function captureRequest()
    {
        foreach ($this->logVars as $var) {
            if (!isset($this->request[$var])) {
                $method = "prepare" . ucfirst($var);
                if (method_exists($this, $method)) {
                    $this->request[$var] = $this->$method();
                } else if (isset($GLOBALS[$var])) {
                    $this->request[$var] = $GLOBALS[$var];
                }
            }
        }

        // Capture only the non-empty elements
        $request = array_filter($this->request, function ($v) {
            return !empty($v);
        });

        $this->write(self::TITLE_REQUEST, $request);
    }

    protected function prepareHeader()
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

    protected function prepareQuery()
    {
        return $_GET;
    }

    protected function prepareForm()
    {
        return $_POST;
    }

    protected function prepareCookie()
    {
        return $_COOKIE ?? [];
    }

    protected function prepareSession()
    {
        return $_SESSION ?? [];
    }

    protected function end()
    {
        $this->writer->end();
    }

    public static function __callStatic($name, $arguments)
    {
        if (!static::$instance || !static::$instance->debug) return;
        $method = "_$name";
        if (method_exists(static::$instance, $method)) {
            static::$instance->$method(...$arguments);
        } else {
            throw new \Exception('Call to undefined method' . __CLASS__ . "::$name");
        }
    }

    /**
     * Add capture log
     * @param string $title
     * @param mixed $data
     */
    protected function _add(string $title, $data)
    {
        if (!static::$instance) return;

        static::$instance->write($title, $data);
    }

    protected function start()
    {
        $this->writer->start();
    }

    protected function write($title, $data)
    {
        $this->writer->write([$title, $data]);
    }
}

