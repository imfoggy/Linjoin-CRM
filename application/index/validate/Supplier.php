<?php
namespace app\index\validate;
use think\Validate;

class Supplier extends Validate{
	protected $rule =   [
        'property_id'  => 'require',
        'sn'  => 'require',
        'name'=> 'require'
    ];

    protected $message  =   [
        'property_id.require' => '请选择供应商性质',
        'sn.require' => '请输入供应商编号',
        'name.require'     => '请输入供应商名称'
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['property_id', 'sn', 'name'],
    ];
}