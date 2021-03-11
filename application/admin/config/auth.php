<?php
/**
 * auth配置文件
 */

return [
    'auth_on'           => true,                // 认证开关
    'auth_type'         => 1,                   // 认证方式，1为实时认证；2为登录认证。
    'auth_group'        => 'group',             // 权限组数据表名
    'auth_group_access' => 'group_access', // 用户-用户组关系表
    'auth_rule'         => 'rule',              // 权限规则表
    'auth_user'         => 'admin',             // 管理员信息表
    'auth_user_id_field'=> 'id',                // 管理员表ID字段名
];