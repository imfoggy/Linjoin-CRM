<?php
namespace app\index\validate;
use think\Validate;

class Customer extends Validate{
	protected $rule =   [
        'from_id'  => 'require',
        'company'  => 'require',
        'property_id'=> 'require',
        'userid'=> 'require',
        'sn'=> 'require',
        'status'=> 'require',
        'industry_id'=> 'require'
    ];

    protected $message  =   [
        'from_id.require' => '请选择一个客户来源',
        'company.require' => '请输入公司名称',
        'property_id.require'     => '请选择公司性质',  
        'userid.require'=> '请选择一个负责人',
        'sn.require'=> '客户编号不能为空',
        'status.require'=> '请选择客户状态',
        'industry_id.require'     => '请选择客户行业',
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['from_id', 'company', 'property_id', 'userid', 'sn', 'status', 'industry_id'],
    ];
}