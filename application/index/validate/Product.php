<?php
namespace app\index\validate;
use think\Validate;

class Product extends Validate{
	protected $rule =   [
        'userid'=> 'require',
        'supplier_id'  => 'require',
        'product_name'  => 'require',
        'product_category_id'=> 'require',
        'unit_id'=> 'require',
        'sn'=> 'require',
        'is_sample'=> 'in:y,n',
        'is_sku'=> 'in:y,n'
    ];

    protected $message  =   [
        'userid.require' => '登录过期，请重新登录',
        'supplier_id.require' => '请选择一个供应商',
        'product_name.require'     => '请输入产品名称',
        'product_category_id'=> '请选择一个商品分类',
        'unit_id'=> '请选择商品单位',
        'sn'=> '请填写商品编码',
        'is_sample.in'=> '请选择是否有样品',
        'is_sku'=> '请选择商品是否具有多规格属性'
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['userid', 'supplier_id', 'product_name', 'product_category_id', 'unit_id', 'sn', 'is_sample', 'is_sku'],
    ];
}