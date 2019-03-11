<?php
namespace app\index\validate;
use think\Validate;

class Clue extends Validate{
	protected $rule =   [
        'from_id'  => 'require',
        'name'  => 'require',
        'company'  => 'require',
        'position'  => 'require',
        'sex'  => 'in:w,m',
        'mobile'  => 'require|mobile',
        'email'  => 'require|email'
    ];

    protected $message  =   [
        'from_id.require' => '请选择一个客户来源',
        'name.require'     => '请输入客户名称',
        'company.require' => '请输入公司名称',
        'position.require'     => '请输入职务',  
        'sex.in'=> '请选择一个尊称',
        'mobile.require'=> '请输入手机号码',
        'mobile.mobile'     => '请输入正确的手机号码',
        'email.require'=> '清输入邮箱',
        'email.email'     => '请输入正确的邮箱地址',
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['from_id', 'name', 'company', 'position', 'sex', 'mobile', 'email'],
    ];
}