<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-11-27
 */

namespace fk\helpers\privilege;

class AclManager
{
    protected $menus;

    public function __construct(array $menus)
    {
        $this->menus = $menus;
    }

    /**
     * Menus accessible according given `privileges`
     * @param string|array $privileges
     * @param null|string $delimiter
     * @return array
     */
    public function accessibleMenus($privileges, $delimiter = null)
    {
        if (is_string($privileges)) $privileges = $delimiter ? explode($delimiter, $privileges) : [$privileges];

        $menus = $this->menus;

        return $this->_accessibleReduce($menus, $privileges);
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
     * @param array|string $privileges
     *  1. `array` Privilege array <br>
     *  2. `string` One privilege <br>
     *  3. `$delimiter separated string` array of privileges joined by $delimiter, an explode will be used
     * @param null|string $delimiter Used to explode `$privilege` when it's a string
     * @return array|true True for all listed, and array for leftovers
     */
    public function exits($privileges, $delimiter = null)
    {
        if (is_string($privileges)) $privileges = $delimiter ? explode($delimiter, $privileges) : [$privileges];

        $this->_existsReduce($this->menus, $privileges);

        return $privileges ?: true;
    }

    private function _existsReduce($menus, &$privileges)
    {
        foreach ($menus as $item) {
            if (isset($item['url']) && false !== $key = array_search($item['url'], $privileges, true)) {
                unset($privileges[$key]);
                if (empty($privileges)) return true;
            }

            if (!empty($item['children']) && $this->_existsReduce($item['children'], $privileges) === true) {
                return true;
            }
        }
        return false;
    }
}