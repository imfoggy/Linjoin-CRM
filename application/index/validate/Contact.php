<?php
namespace app\index\validate;
use think\Validate;

class Contact extends Validate{
	protected $rule =   [
        'customer_id'  => 'require',
        'name'  => 'require',
        'contact_role_id'=> 'require',
        'sex'=> 'require',
        'position'=> 'require',
        'phone'=> 'require',
        'email'=> 'require'
    ];

    protected $message  =   [
        'customer_id.require' => '请选择一个关联客户',
        'name.require' => '请输入联系人姓名',
        'contact_role_id.require'     => '请选择联系人角色',  
        'sex.require'=> '请选择联系人性别',
        'position.require'=> '请输入联系人职位',
        'phone.require'=> '请输入联系人手机号',
        'email.require'     => '请输入联系人邮箱',
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['customer_id', 'name', 'contact_role_id', 'sex', 'position', 'phone', 'email'],
    ];
}