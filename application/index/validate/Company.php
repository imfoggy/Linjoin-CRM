<?php
namespace app\index\validate;
use think\Validate;

/**
 * 定义一个用户验证类
 */
class Company extends Validate
{

    /**
     * 定义验证规则
     * @var [type]
     */
    protected $rule =   [
    	'company'	=> 'require|max:200',
        'username'  => 'require|max:25|unique:company',
        'password'  => 'require|max:25|alphaNum',
        // 'start_time'=> 'dateFormat:y-m-d',
        // 'end_time'	=> 'dateFormat:y-m-d',
        'is_ban'  => 'in:0,1',
    ];
    
    protected $message  =   [
        'company.require' => '请输入公司名称',
        'username.require'     => '请输入账号',
        'username.max'	=> '用户账号超过了最大限制长度',
        'username.uniqid'	=> '已经存在相同的账号了',
        'password.require' => '请输入密码',
        'password.max'     => '密码长度最多不能超过25个字符',  
        'password.alphaNum'=> '密码中只能包含字母和数字',
        // 'start_time.dateFormat'=> '开始日期格式不正确',
        // 'end_time.dateFormat'	=> '结束日期不正确',

        'is_ban.in'=> '请正确选择账号状态'
    ];

    protected $field = [
        'username'  => '账号',
    ];

    protected $scene = [
        'create'=> ['company','username', 'password', 'is_ban'],
    ];
}