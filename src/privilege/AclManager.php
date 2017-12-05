<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-27
 */

namespace fk\helpers\privilege;

class AclManager
{

    public const METHOD_GET = 0B1;
    public const METHOD_POST = 0B10;
    public const METHOD_PUT = 0B100;
    public const METHOD_DELETE = 0B1000;
    public const METHOD_PATCH = 0B10000;
    public const METHOD_LOCK = 0B100000;
    public const METHOD_UNLOCK = 0B1000000;
    public const METHOD_LINK = 0B10000000;
    public const METHOD_UNLINK = 0B100000000;
    public const METHOD_ALL = -1;

    public $delimiter = ',';

    protected $menus;

    protected const THROUGH_WITH_KEY = 0B1;
    protected const THROUGH_WITH_VALUE = 0B10;


    public function __construct(array $menus)
    {
        $this->menus = $menus;
    }

    /**
     * Menus accessible according given `urls` of frontend
     * @param string|array $urls
     * @return array
     */
    public function accessibleMenus($urls)
    {
        if (is_string($urls)) $urls = explode($this->delimiter, $urls);

        $menus = $this->menus;

        return $this->_accessibleReduce($menus, $urls);
    }

    private function _accessibleReduce(array &$menus, array $privileges)
    {
        foreach ($menus as $k => &$item) {
            if (isset($item['url']) && !in_array($item['url'], $privileges)) {
                unset($item['url']);
            }

            if (!empty($item['children']) && is_array($item['children'])) {
                $this->_accessibleReduce($item['children'], $privileges);
                if (!empty($item['children'])) $item['children'] = array_values($item['children']);
            }

            if (empty($item['url']) && empty($item['children'])) unset($menus[$k]);
        }
        return $menus ? array_values($menus) : [];
    }

    /**
     * Check if given privileges are all listed in `menus`
     * @param array|string $urls
     *  1. `array` Privilege array <br>
     *  2. `string` One privilege <br>
     *  3. `$delimiter separated string` array of privileges joined by $delimiter, an explode will be used
     * @param array $invalid The leftovers
     * @return bool Whether all URLs exist
     */
    public function exists($urls, &$invalid = [])
    {
        if (is_string($urls)) $urls = explode($this->delimiter, $urls);

        $urls = $this->unique($urls);

        $this->goThrough(function ($item) use (&$urls) {
            if (false !== $key = array_search($item['url'], $urls)) {
                unset($urls[$key]);
            }
        });

        return [] === $invalid = $urls;
    }

    public function unique($urls)
    {
        $isString = is_string($urls);

        $urls = $isString ? explode($this->delimiter, $urls) : $urls;

        array_walk($urls, function (&$v) {
            $v = trim($v);
        });
        $urls = array_filter(array_unique($urls));

        return $isString ? implode($this->delimiter, $urls) : $urls;
    }

    /**
     * Translate `$urls` into API `privileges` with format
     *
     * **Example**
     *
     * [apis => ['session' => 1|2]]
     * [apis => ['role' => -1]]
     *
     * ```
     *  [
     *      'session' => 3,
     *      'role' => -1,
     *  ]
     * ```
     * @param string|array $urls
     * @return array
     */
    public function urlsToPrivileges($urls)
    {

        if (is_string($urls)) $urls = explode($this->delimiter, $urls);

        $privileges = [];

        $this->goThrough(function ($item) use (&$urls, &$privileges) {
            if (isset($item['url'], $item['apis']) && in_array($item['url'], $urls)) {
                foreach ($item['apis'] as $api => $methods) {
                    $api = trim($api);
                    $methods = (int)$methods;
                    if (!isset($privileges[$api])) $privileges[$api] = 0;
                    $privileges[$api] |= $methods;
                }
            }
        });
        return $privileges;
    }

    protected function goThrough(callable $callback, $with = self::THROUGH_WITH_VALUE)
    {
        $this->menuIterator($this->menus, $callback, $with);
    }

    protected function menuIterator(array $menus, callable $callback, $with)
    {
        foreach ($menus as $key => $item) {
            if (isset($item['children']) && is_array($item['children'])) $this->menuIterator($item['children'], $callback, $with);

            $params = [];
            if ($with & self::THROUGH_WITH_KEY) $params[] = $key;
            if ($with & self::THROUGH_WITH_VALUE) $params[] = $item;

            if (isset($item['url']) && false === call_user_func_array($callback, $params)) break;
        }
    }

    public function translateMethod(string $method)
    {
        return (new \ReflectionClass(static::class))->getConstant("METHOD_$method");
    }

    /**
     * Check if methods allowed contains current request method
     * @param int $methodsAllowed
     * @return bool
     */
    public function authenticated($methodsAllowed)
    {
        $methodsAllowed = (int)$methodsAllowed;
        if (!$methodsAllowed) return false;

        if (false === $current = $this->translateMethod($_SERVER['REQUEST_METHOD'])) return false;

        return boolval($methodsAllowed & $current);
    }

}
