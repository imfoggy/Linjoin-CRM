<?php
namespace app\index\validate;
use think\Validate;
/**
 * 定义一个用户验证类
 */
class User extends Validate
{

    /**
     * 定义验证规则
     * @var [type]
     */
    protected $rule =   [
        'username'  => 'require|max:25',
        'password'  => 'require|max:25|alphaNum',

        'department_id'=> 'require|number',
        'group_id'=> 'require|number',
        'sn'=> 'require',
        'nickname'  => 'require',
        'email' => 'require|email',
        'phone'=> 'require|mobile',
        'sex'  => 'in:w,m',
    ];
    
    protected $message  =   [
        'username.require' => '请输入账号',
        'username.max'     => '账号长度最多不能超过25个字符',
        'username.unique' => '账号名已经存在',
        'password.require' => '请输入密码',
        'password.max'     => '密码长度最多不能超过25个字符',  
        'password.alphaNum'=> '密码中只能包含字母和数字',

        'department_id.require'=> '请选择一个部门',
        'department_id.number'=> '部门格式不正确',
        'group_id.require'=> '请选择一个岗位',
        'group_id.number'=> '岗位格式不正确',
        'sn.require'=> '请输入员工编号',
        'nickname.require'=> '请输入用户的真实姓名',
        'email.require'=> '请输入用户邮箱',
        'email.email'=> '请输入正确的邮箱地址',
        'phone.require'=> '请输入手机号码',
        'phone.mobile'=> '请输入正确的手机号码',
        'sex.in'=> '请正确选择用户性别'
    ];

    protected $field = [
        'username'  => '账号',
    ];

    protected $scene = [
        #登录场景验证
        'login'  =>  ['username', 'password'],
        'create'=> ['username','password', 'department_id', 'group_id', 'sn', 'nickname', 'email', 'phone', 'sex'],
        'edit'  =>  ['department_id', 'group_id', 'sn', 'nickname', 'email', 'phone', 'sex'],
        //超级管理员开通账号
        'super'=> ['username', 'password', 'phone', 'department_id', 'group_id', 'email']
    ];

    /**
     * create场景验证追加验证规则
     * @Author Foggy
     * @Date   2018-11-13
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function sceneCreate(){
        return $this->only(['username','password', 'department_id', 'group_id', 'sn', 'nickname', 'email', 'phone', 'sex'])
            ->append('email', 'unique:users')
            ->append('username','unique:users');
    }

    /**
     * edit场景追加验证规则
     * @Author Foggy
     * @Date   2018-12-25
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function sceneEdit(){
        return $this->only(['department_id', 'group_id', 'sn', 'nickname', 'email', 'phone', 'sex', 'email'])
            ->remove('email', 'unique:users');
    }

    /**
     * 开通账号场景验证追加验证规则
     * @Author Foggy
     * @Date   2018-11-13
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function sceneSuper(){
        return $this->only(['username','password', 'phone', 'department_id', 'group_id'])
            ->append('email','unique:users')
            ->append('username','unique:users');
    }
}