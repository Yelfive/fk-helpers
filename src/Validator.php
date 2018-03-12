<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-30
 */

namespace fk\helpers;

use Exception;

/**
 * This validator is mainly for `fk\helpers\Result`
 * @see \fk\helpers\Result
 */
class Validator
{

    /**
     *  [
     *      'field' => 'required',
     *      'field' => callable,
     *  ]
     * @var array
     */
    protected $rules;

    private $error;

    protected $messages = [
        'required' => '`{name}` is required, {value} given',
        'array' => '`{name}` must be array, {type} given',
        'indexed' => '`{name}` must be indexed array, type({value}) given',
        'list' => '`{name}` must be 2-dimensional indexed array, and second dimension of which should be associated ones',
        'associated' => '`{name}` must be associated array or instance of stdClass, {type}({value}) given',
        'string' => '`{name}` must be string, {type} given',
        'integer' => '`{name}` must be integer, {type} given',
        'max' => '`{name}` must be less than or equal to {args}, {value} given',
        'min' => '`{name}` must be greater than or equal to {args}, {value} given',
    ];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public static function build(array $rules)
    {
        return new static($rules);
    }

    public function validate(array $data)
    {
        $this->error = '';
        foreach ($data as $k => $v) {
            foreach ($this->rules[$k] ?? [] as $rule) {
                if (is_callable($rule)) {
                    $validator = $rule;
                    $args = [];
                } else {
                    $args = explode(':', $rule);
                    $rule = array_shift($args);
                    $method = 'check' . ucfirst($rule);
                    if (!method_exists($this, $method)) throw new Exception("Rule not exists: $rule");
                    $validator = [$this, $method];
                }
                if (false === call_user_func($validator, $v, ...$args)) {
                    $replaces = [
                        '{name}' => $k,
                        '{type}' => gettype($v),
                        '{value}' => $this->value($v, $rule),
                        '{args}' => implode(',', $args)
                    ];
                    $this->error = isset($this->messages[$rule]) ? str_replace(array_keys($replaces), $replaces, $this->messages[$rule]) : '';
                    return false;
                }
            }
        }
        return true;
    }

    public function value($value, $rule): string
    {
        if ($value === '') return 'empty string';
        if ($rule === 'associated' && is_array($value) && $this->checkIndexed($value)) return 'indexed array';
        if ($rule === 'indexed' && is_array($value) && !$this->checkIndexed($value)) return 'associated array';

        return Dumper::dump($value, true);
    }

    public function error()
    {
        return $this->error;
    }

    protected function checkList($value)
    {
        $index = 0;
        foreach ($value as $k => $v) {
            if ($k !== $index++ || !is_array($v) || $this->checkIndexed($v)) return false;
        }
        return true;
    }

    protected function checkRequired($value): bool
    {
        return $value !== '' && $value !== null;
    }

    protected function checkArray($value): bool
    {
        return is_array($value);
    }

    protected function checkIndexed($value): bool
    {
        if (!is_array($value)) return false;

        $index = 0;

        foreach ($value as $k => $v) {
            if ($index++ !== $k) return false;
        }
        return true;
    }

    protected function checkAssociated($value)
    {
        return $value === [] || is_array($value) && !$this->checkIndexed($value) || $value instanceof \stdClass;
    }

    protected function checkString($value): bool
    {
        return is_string($value);
    }

    protected function checkInteger($value): bool
    {
        return is_int($value);
    }

    protected function checkMax($value, $compareWith): bool
    {
        return $value <= $compareWith;
    }

    protected function checkMin($value, $compareWith): bool
    {
        return $value >= $compareWith;
    }

}