<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 *
 * This is a template for menu definition
 *
 * @see docs/AclManager.md
 */

use fk\helpers\privilege\AclManager as ACL;

return [
    ['label' => '油站管理', 'children' => [
        ['label' => '合作单位', 'icon' => 'icon-share', 'url' => 'l.station_unit', 'apis' => ['company' => ACL::METHOD_ALL, 'company/{id}' => ACL::METHOD_ALL]],
        ['label' => '信息管理', 'icon' => 'icon-notebook', 'url' => 'l.station_oil', 'apis' => ['station' => ACL::METHOD_ALL, 'station/{id}' => ACL::METHOD_ALL]],
        ['label' => '油枪配置', 'icon' => 'icon-wrench', 'url' => 'l.station_gun', 'apis' => ['price' => ACL::METHOD_ALL, 'price/{id}' => ACL::METHOD_ALL]],
        ['label' => '打印机配置', 'icon' => 'icon-printer', 'url' => 'l.station_printer', 'apis' => ['printer' => ACL::METHOD_ALL, 'printer/{id}' => ACL::METHOD_ALL]],
        ['label' => '班次管理', 'icon' => 'fa fa-users', 'url' => 'l.station_staff', 'apis' => ['shift' => ACL::METHOD_ALL, 'shift/{id}' => ACL::METHOD_ALL, 'shift/work/{id}' => ACL::METHOD_POST]],
        ['label' => '市场油价', 'icon' => 'fa fa-money', 'url' => 'l.station_money', 'apis' => []],
    ]],
    ['label' => '订单管理', 'children' => [
        ['label' => '订单总览', 'icon' => 'icon-list', 'url' => 'l.order_survey', 'apis' => ['order' => ACL::METHOD_GET, 'order/{id}' => ACL::METHOD_ALL, 'station' => ACL::METHOD_GET, 'company' => ACL::METHOD_GET]],
        ['label' => '退款管理', 'icon' => 'icon-action-undo', 'url' => 'l.order_refund', 'apis' => ['refund' => ACL::METHOD_GET | ACL::METHOD_POST, 'refund/{id}' => ACL::METHOD_GET, 'refund/{id}/approve' => ACL::METHOD_PUT, 'refund/{id}/reject' => ACL::METHOD_PUT]],
        ['label' => '退款历史', 'icon' => 'fa fa-history', 'url' => 'l.order_refund_history', 'apis' => ['refund/history' => ACL::METHOD_GET]],
        ['label' => '对账', 'icon' => 'fa fa-history', 'url' => 'l.order_statement', 'apis' => ['accounting' => ACL::METHOD_GET]],
        ['label' => '交班记录', 'icon' => 'fa fa-suitcase', 'url' => 'l.order_handover', 'apis' => ['shifting/history' => ACL::METHOD_GET, 'shifting/history/{id}' => ACL::METHOD_GET]],
    ]],
    ['label' => '权限管理', 'children' => [
        ['label' => '账户管理', 'icon' => 'icon-user', 'url' => 'l.auth_admin', 'apis' => ['admin' => ACL::METHOD_ALL, 'role/privilege' => ACL::METHOD_GET, 'admin/{id}' => ACL::METHOD_ALL]],
        ['label' => '角色管理', 'icon' => 'icon-user', 'url' => 'l.auth_role', 'apis' => ['role' => ACL::METHOD_ALL, 'role/privilege' => ACL::METHOD_GET, 'role/{id}' => ACL::METHOD_ALL]],
        ['label' => '用户管理', 'icon' => 'icon-users', 'url' => 'l.auth_user', 'apis' => ['user' => ACL::METHOD_ALL, 'user/{id}' => ACL::METHOD_ALL]],
    ]],
];