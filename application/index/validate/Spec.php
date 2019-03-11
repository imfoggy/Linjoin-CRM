<?php
namespace app\index\validate;
use think\Validate;

class Spec extends Validate{
	protected $rule =   [
        'product_category_id'  => 'require',
        'name'  => 'require',
        'value'=> 'require'
    ];

    protected $message  =   [
        'product_category_id.require' => '请选择产品分类',
        'name.require' => '请输入规格名称',
        'value.require'     => '请输入规格值'
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['product_category_id', 'name', 'value'],
    ];
}