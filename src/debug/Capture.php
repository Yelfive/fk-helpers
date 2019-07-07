<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-05-29
 */

namespace fk\helpers\debug;

/**
 * Class DebugRequestCapture
 * @package fk\helpers
 *
 * @method static $this add(array $data)
 * @method static $this softAdd(array $data)
 *
 * @method $this header(array | callable $headers)
 * @method $this query(array | callable $queryParams)
 * @method $this form(array | callable $formData)
 * @method $this session(array | callable $session)
 * @method $this file(array | callable $files)
 */
class Capture
{

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

    /**
     * @var array
     */
    protected $soft = [];

    protected $logVars = ['header', 'query', 'form', 'session', 'file'];

    public function __construct(WriterInterface $writer, bool $debug = true)
    {
        if (!$debug) return;

        $this->debug = $debug;
        static::$instance = $this;
        $this->writer = $writer;
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
        } else if (method_exists($this, "_$name")) {
            $method = "_$name";
            $this->$method($arguments[0]);
            return $this;
        }
        throw new \Exception("Calling undefined method $name of " . __CLASS__);
    }

    /**
     * Allow static call for methods inside this class
     * @param string $name
     * @param array $arguments
     * @return $this|null
     */
    public static function __callStatic($name, $arguments)
    {
        if (!static::$instance || !static::$instance->debug) return null;
        $method = "_$name";
        return static::$instance->__call($method, $arguments);
    }

    protected function call($callback)
    {
        return is_callable($callback) ? call_user_func($callback) : $callback;
    }

    public function capture()
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

        $this->write($request);
    }

    protected function prepareHeader()
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
        return $_FILES ?: file_get_contents('php://input');
    }

    protected function prepareSession()
    {
        return $_SESSION ?? [];
    }

    /**
     * Add capture log
     * @param mixed $data
     */
    protected function _add(array $data)
    {
        $this->write($data);
    }

    protected function _softAdd(array $data)
    {
        $this->soft = array_merge($this->soft, $data);
    }

    protected function write($data)
    {
        if (!$this->debug || !$this->writer) return null;

        $data = array_merge($this->soft, $data);
        $this->soft = [];
        $this->writer->write($data);
    }
}

