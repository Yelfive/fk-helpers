<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-30
 */

namespace fk\helpers;

use fk\http\StatusCode;

/**
 * @method array getRules()
 * @method $this rules(array $rules)
 * @method $this debug(bool $debug = true)
 */
class ResultConfig
{

    /**
     * Debug mode will strictly validate the attributes with there rules.
     * And turn off the debug mode will improve the performance
     * by not checking some performance consuming rules
     * @var bool
     */
    protected $debug = true;

    protected $rules = [
        'code' => ['required', 'integer', 'min:100', 'max:599'],
        'message' => ['required', 'string'],
        'list' => ['array', 'list'],
        'data' => ['associated'],
    ];

    protected function _debug(bool $debug = true)
    {
        $this->debug = $debug;
    }

    protected function _rules(array $rules)
    {
        $this->rules = $rules;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, "_$method")) {
            if ($arguments && $arguments[0] !== null) $this->{"_$method"}($arguments[0]);
        } else if (strncmp($method, 'get', 3) === 0) {
            return $this->{lcfirst(substr($method, 3))};
        } else {
            throw new Exception("Calling to undefined method $method");
        }

        return $this;
    }
}

/**
 * @method $this code(int $statusCode) Set code, which must be integer
 * @method $this message(string $message) Set message
 * @method $this data(array | \stdClass $associated) Set data, which must be associated array
 * @method $this list(array $indexed)   Set list, which must be indexed array
 */
class Result
{

    use BuildTrait;

    protected static $instance;

    /**
     * @var Validator
     */
    protected static $validator;

    protected $response = ['code' => StatusCode::SUCCESS_OK, 'extend' => []];

    /**
     * @var ResultConfig
     */
    protected $config;

    public function __construct()
    {
        $this->config = new ResultConfig();
        if (!static::$validator) static::$validator = new Validator($this->config->getRules());
    }

    public function configBag()
    {
        return $this->config;
    }

    public function __call($name, $arguments)
    {
        $value = $arguments[0] ?? null;
        // `null` value is reserved for retrieving data only
        if ($value === null) {
            return $this->response[$name];
        } else if (!$this->ruleExits($name)) {
            $this->extend($name, $value);
        } else if ($this->validate([$name => $value])) {
            $this->response[$name] = $value;
        }
        return $this;
    }

    protected function ruleExits($name)
    {
        return isset($this->config->getRules()[$name]);
    }

    /**
     * Add data to response, with its `$name` as key
     * @param string|array $name
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function extend($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) $this->response['extend'][$k] = $v;
        } else {
            $this->response['extend'][$name] = $value;
        }
        $this->validate($this->response['extend']);
        return $this;
    }

    /**
     * Overwrite the `extend` of response with `$data`
     * @param array $data
     * @return $this
     */
    public function overwriteExtend(array $data)
    {
        $this->validate($data);
        $this->response['extend'] = $data;
        return $this;
    }

    /**
     * Unset key `$name` from response
     * @param string $name
     * @return $this
     */
    public function clear(string $name)
    {
        unset($this->response[$name]);
        return $this;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Exception
     */
    protected function validate(array $data)
    {
        if (static::$validator->validate($data)) return true;

        $error = static::$validator->error();
        $this->exception($error);
    }

    /**
     * @param string $error
     * @throws Exception
     */
    protected function exception(string $error)
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $caller = $traces[1] ?? [];
        $message = "Invalid response with error: $error\n";
        if ($caller) $message .= $this->bolder("Check this out\n") . "#0 " . $this->red($caller['file']) . " on line " . $this->red($caller['line']);
        throw new Exception($message);
    }

    protected function red($message)
    {
        if (PHP_SAPI === 'cli') {
            return "\033[31m$message\033[0m";
        } else {
            return $message;
        }
    }

    protected function bolder($message)
    {
        if (PHP_SAPI === 'cli') {
            return "\033[1m$message\033[0m";
        } else {
            return $message;
        }
    }

    public function toArray(): array
    {
        $response = $this->response;

        $extend = $response['extend'];
        unset($response['extend']);

        $data = $response + $extend;

        if (isset($data['data']) && is_array($data['data']) && empty($data['data'])) $data['data'] = new \stdClass();

        return $data;
    }

    public function toJson(): string
    {
        $response = $this->toArray();

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
