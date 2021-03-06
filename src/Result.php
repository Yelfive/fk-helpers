<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-30
 */

namespace fk\helpers;

use fk\http\StatusCode;

/**
 * @method array getRules() Return current rules
 * @method $this rules(array $rules) Set rules to overwrite default validation
 * @method $this debug(bool $debug = true) Set if in debug mode, which will do some performance consuming validation
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
        if ($this->debug = $debug) {
            $this->rules['list'] = ['array'];
            $this->rules['data'] = ['array'];
        }
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

    use SingletonTrait;

    /**
     * @var Validator
     */
    protected static $validator;

    protected $defaultResponse = ['code' => StatusCode::SUCCESS_OK, 'extend' => []];

    protected $response;

    /**
     * @var ResultConfig
     */
    protected $config;

    public function __construct()
    {
        $this->config = new ResultConfig();
        if (!static::$validator) static::$validator = new Validator($this->config->getRules());
        $this->clear();
    }

    public function propertyBag()
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
     * @param string|array|null $name
     * * string - `$value` must not be null <br>
     * * array  - `$value` will be ignored <br>
     * * null   -  It will be taken as retrieving<br>
     *
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    public function extend($name = null, $value = null)
    {
        if (is_array($name)) {
            $this->merge($this->response['extend'], $name);
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
     * @param string|null $name
     * @return $this
     */
    public function clear(string $name = null)
    {
        if ($name === null) {
            $this->response = $this->defaultResponse;
        } else {
            unset($this->response[$name]);
        }
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
        $caller = $traces[2] ?? [];
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

    public function hasResponse()
    {
        return isset($this->response['message']);
    }

    public function toArray(): array
    {
        $response = $this->response;

        $extend = $response['extend'];
        unset($response['extend']);

        $this->merge($response, $extend);

        if (isset($response['data']) && is_array($response['data']) && empty($response['data'])) $response['data'] = new \stdClass();

        $this->validate(['message' => $response['message'] ?? null]);

        return $response;
    }

    protected function merge(array &$dst, array $source)
    {
        foreach ($source as $k => $v) $dst[$k] = $v;
    }

    public function toJson(): string
    {
        $response = $this->toArray();

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
