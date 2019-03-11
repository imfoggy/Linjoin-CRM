<?php
namespace app\index\validate;
use think\Validate;

class SendSample extends Validate{

	protected $rule =   [
        'receive_username'  => 'require',
        'receive_address'  => 'require',
        'receive_phone'  => 'require',
        'class_name'  => 'require',
        'model_name'  => 'require',
        'weight'  => 'require|float',
        'number'  => 'require|integer',
        'express'  => 'require',
        'express_pay_type'  => 'in:0,1',
        'send_user_id'  => 'require',
        'send_time'  => 'require|date'
    ];

    protected $message  =   [
        'receive_username.require'     => '请输入发件人名称',
        'receive_address.require' => '请输入发件地址',
        'receive_phone.require'     => '请输入发件人联系电话',  
        'class_name.require'=> '请输入样品名称',
        'model_name.require'=> '请输入样品型号',
        'weight.require'     => '请输入样品重量',
        'weight.float'=> '请正确输入样品重量',
        'number.require'     => '请输入样品数量',
        'number.integer'    => '请正确输入样品件数',
        'express.require' => '请输入快递公司',
        'express_pay_type.in'     => '请正确选择快递付款方式',
        'send_user_id.require' => '请选择收件人',
        'send_time.require'     => '请选择收件时间'
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  ['receive_username', 'receive_address', 'receive_phone', 'class_name', 'model_name', 'weight','number','express','express_pay_type','send_user_id','send_time'],
        'edit'=> ['receive_username', 'receive_address', 'receive_phone', 'class_name', 'model_name', 'weight','number','express','express_pay_type','send_user_id','send_time']
    ];
}