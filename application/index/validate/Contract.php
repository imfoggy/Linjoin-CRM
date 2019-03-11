<?php
namespace app\index\validate;
use think\Validate;

class Contract extends Validate{
	protected $rule =   [
        'create_userid'=> 'require',
        'sn'=> 'require',
        'name'=> 'require',
        'signatory_userid'  => 'number',
        'approvers_userid'  => 'number',
        'from_customer_id'  => 'number',
        'money'  => 'egt:0',
        'sign_time'  => 'date',
        'effect_time'  => 'date',
        'price_clause'  => 'in:FOB,CIF,CFR',
        'price_clause_port'  => 'require',
        'payment'  => 'in:1,2,3,4',
        //'payment_desc'  => 'require',
        'shipment_time'  => 'date',
        'send_address'  => 'require',
        'start_port'=> 'require',
        'start_port_country'  => 'require',
        'end_port'  => 'require',
        'end_port_country'  => 'require',
        'arrival_time'  => 'date'
    ];

    protected $message  =   [
        'create_userid.require' => '无法获取到登录信息，请重新登录',
        'sn.require'     => '请输入合同编号',
        'name.require' => '请输入合同名称',
        'signatory_userid.number'     => '请选择合同签约人',  
        'approvers_userid.number'=> '请选择合同审批人',
        'from_customer_id.number'=> '请选择一个客户来源',
        'money.egt'     => '请正确填写合同金额',
        'sign_time.date'=> '请正确选择合同的签约时间',
        'effect_time.date'     => '请正确选择合同的生效日期',
        'price_clause.in' => '请选择一个价格条款',
        'price_clause_port.require'     => '请填写价格条款下的港口信息',
        'payment.in' => '请选择支付方式',
        //'payment_desc.require'     => '请正确填写支付方式下面的各项参数',  
        'shipment_time.date'=> '请正确选择一个装运时间',
        'send_address.require'=> '请填写发货地址',
        'start_port.require'=> '请正确选择起运港口国家',
        'start_port_country.require'=> '请正确选择起运港口国家',
        'end_port'=> '去正确选择结束港口国家。',
        'end_port_country.require' => '请正确选择目的地港口国家',
        'arrival_time.date'=> '请正确选择预计到港时间',
    ];

    protected $scene = [
        #登录场景验证
        'create'  =>  [
            'create_userid', 
            'sn', 
            'name', 
            //'signatory_userid',
            //'approvers_userid', 
            'from_customer_id', 
            'money',
            //'sign_time',
            //'effect_time',
            //'price_clause',
            //'price_clause_port',
            //'payment',
            //'payment_desc',
            //'shipment_time',
            //'send_address',
            //'start_port',
            //'start_port_country',
            //'end_port',
            //'end_port_country',
            //'arrival_time'
        ],
    ];
}