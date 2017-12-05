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
    ['label' => '基本操作', 'children' => [
        ['label' => '管理员登陆', 'url' => 'account.signin', 'api' => 'session', 'methods' => ACL::METHOD_POST],
        ['label' => '注销登陆', 'url' => 'account.signout', 'api' => 'session', 'methods' => ACL::METHOD_DELETE],
    ]],
    ['label' => '油站管理', 'children' => [
        ['label' => '合作单位', 'url' => 'l.station_unit', 'api' => 'company', 'methods' => ACL::METHOD_ALL],
        ['label' => '信息管理', 'children' => [
            ['label' => '列表', 'url' => 'l.station_oil', 'api' => 'station', 'methods' => ACL::METHOD_GET],
            ['label' => '添加', 'url' => 'l.station_oil_info', 'api' => 'station', 'methods' => ACL::METHOD_POST],
            ['label' => '编辑', 'url' => 'l.station_oil_info', 'api' => 'station', 'methods' => ACL::METHOD_PUT],
        ]],
        ['label' => '油枪配置', 'url' => 'l.station_gun', 'api' => 'price', 'methods' => ACL::METHOD_ALL],
        ['label' => '打印机配置', 'url' => 'l.station_printer', 'api' => 'printer', 'methods' => ACL::METHOD_ALL],
        ['label' => '班次管理', 'url' => 'l.station_staff', 'api' => 'shift', 'methods' => ACL::METHOD_ALL],
    ]],
    ['label' => '订单管理', 'children' => [
        ['label' => '订单总览', 'url' => 'l.order_survey', 'api' => 'order', 'methods' => ACL::METHOD_GET],
        ['label' => '退款管理', 'url' => 'l.order_refund', 'api' => 'refund', 'methods' => ACL::METHOD_GET | ACL::METHOD_PUT | ACL::METHOD_POST],
        ['label' => '退款历史', 'url' => 'l.order_refund_history', 'api' => 'refund/history', 'methods' => ACL::METHOD_GET],
        ['label' => '对账', 'url' => 'l.order_statement', 'api' => 'accounting/check', 'methods' => ACL::METHOD_GET],
        ['label' => '交接班', 'url' => 'l.order_handover', 'api' => 'shift', 'methods' => ACL::METHOD_ALL],
    ]],
    ['label' => '权限管理', 'children' => [
        ['label' => '角色管理', 'url' => 'l.auth_role', 'api' => 'role', 'methods' => ACL::METHOD_ALL, 'children' => [
            ['label' => '权限列表', 'url' => 'l.auth_role_privilege', 'api' => 'role/privilege', 'methods' => ACL::METHOD_GET],
        ]],
        ['label' => '用户管理', 'url' => 'l.auth_user', 'api' => 'user', 'methods' => ACL::METHOD_ALL],
    ]],
];