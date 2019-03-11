<?php
namespace app\index\validate;
use think\Validate;

class Sample extends Validate{

	protected $rule =   [
        'supplier_id'  => 'require',
        'send_username'  => 'require',
        'send_address'  => 'require',
        'send_phone'  => 'require',
        'class_name'  => 'require',
        'model_name'  => 'require',
        'weight'  => 'require|float',
        'number'  => 'require|integer',
        'express'  => 'require',
        'express_pay_type'  => 'in:0,1',
        'receive_user_id'  => 'require',
        'receive_time'  => 'require|date'
    ];

    protected $message  =   [
        'supplier_id.require' => '请选择一个供应商',
        'send_username.require'     => '请输入发件人名称',
        'send_address.require' => '请输入发件地址',
        'send_phone.require'     => '请输入发件人联系电话',  
        'class_name.require'=> '请输入样品名称',
        'model_name.require'=> '请输入样品型号',
        'weight.require'     => '请输入样品重量',
        'weight.float'=> '请正确输入样品重量',
        'number.require'     => '请输入样品数量',
        'number.integer'    => '请正确输入样品件数',
        'express.require' => '请输入快递公司',
        'express_pay_type.in'     => '请正确选择快递付款方式',
        'receive_user_id.require' => '请选择收件人',
        'receive_time.require'     => '请选择收件时间'
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['supplier_id', 'send_username', 'send_address', 'send_phone', 'class_name', 'model_name', 'weight','number','express','express_pay_type','receive_user_id','receive_time'],
        'edit'=> ['supplier_id', 'send_username', 'send_address', 'send_phone', 'class_name', 'model_name', 'weight','number','express','express_pay_type','receive_user_id','receive_time']
    ];
}