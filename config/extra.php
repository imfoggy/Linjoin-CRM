<?php
/**
 * [Auth权限认证设置]
 * @Author Foggy
 * @Date   2018-10-08
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @return array
 */
return [
    'auth' =>[
    	'AUTH_ON'           => true, // 认证开关
    	'AUTH_TYPE'         => 1, // 认证方式，1为实时认证；2为登录认证。
    	'AUTH_USER'         => 'users' // 用户信息表
    ],
];