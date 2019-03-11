<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class Users extends Model{

	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
	protected $insert = ['password','crypt'];
    // 定义全局的查询范围
    protected $globalScope = ['com'];

	protected function setPasswordAttr($value)
    {
        return act_password($value, 'abcdef');
    }

    protected function setCryptAttr($value)
    {
        return 'abcdef';
    }

    /**
     * 新增的时候自动增加com_id
     * @Author Foggy
     * @Date   2018-12-20
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     */
    // protected function setComIdAttr($value){
    //     return session('loginUser.com_id');
    // }

    /**
     * 全局查询条件
     * @Author Foggy
     * @Date   2018-12-20
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $query [description]
     * @return [type]                   [description]
     */
    public function scopeCom($query)
    {
        $query->where('com_id',session('loginUser.com_id'));
    }
	/**
	 * 根据用户名来获取用户信息
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  string            $username [description]
	 * @return [type]                      [description]
	 */
	public static function getUserInfoByName($username = ''){
        //查询用户信息，并关闭全局范围查询，因为用户没有登录，此时session为空，若
        //是带有全局查询条件，查询诗句肯定为空
		return self::useGlobalScope(false)->get(['username'=> $username]);
	}

	/**
	 * 根据用户id来获取用户信息
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  integer           $id [description]
	 * @return [type]                [description]
	 */
	public static function getUserInfoById($id = 0){
		return self::with('department,authGroup')->get($id);
	}

	/**
	 * 根据id来获取用户名称
	 * @Author Foggy
	 * @Date   2018-10-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  integer           $id [description]
	 * @return [type]                [description]
	 */
	public static function getUsernameById($id = 0){
		return self::where(['id'=> $id])->value('username');
	}

	/**
	 * 用户列表
	 * @Author Foggy
	 * @Date   2018-11-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  integer           $userid [description]
	 * @param  array             $where  [description]
	 * @param  integer           $limit  [description]
	 * @return [type]                    [description]
	 */
	public static function getUsers($where = [], $limit = 10){
		$result = self::with('department,authGroup')->where($where)->order('id desc')->paginate($limit)->each(function($item, $key){
			// $item->create_username = db('users')->where(['id'=> $item->create_userid])->value('username');
			// $item->principal_username = db('users')->where(['id'=> $item->userid])->value('username');
		});

		return $result;
	}

	/**
	 * 关联日志表
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function logs(){
        return $this->hasMany('Log', 'userid');
    }

    /**
     * 关联线索表的负责人线索
     * @Author Foggy
     * @Date   2018-10-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function clues(){
    	return $this->hasMany('Clue', 'userid');
    }

    /**
     * 关联客户表的负责人客户
     * @Author Foggy
     * @Date   2018-10-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function customers(){
        return $this->hasMany('Customer', 'userid');
    }

    /**
     * 关联线索表的创建人线索
     * @Author Foggy
     * @Date   2018-10-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function createClues(){
    	return $this->hasMany('Clue', 'create_userid');
    }

    /**
     * 获取到部门信息
     * @Author Foggy
     * @Date   2018-11-09
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function department(){
    	return $this->belongsTo('Department', 'department_id');
    }

    /**
     * 获取到岗位信息
     * @Author Foggy
     * @Date   2018-11-09
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function authGroup(){
    	return $this->belongsTo('AuthGroup', 'group_id');
    }

    /**
     * 获取到已启用的用户
     * @Author Foggy
     * @Date   2018-11-15
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function scopeActiveUser($query){
    	$query->where('status', 1);
    }

    /**
     * 获取到已停用的用户
     * @Author Foggy
     * @Date   2018-11-15
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function scopeBanUser($query){
    	$query->where('status', 2);
    }

    /**
     * 关联日志领导
     * @Author Foggy
     * @Date   2018-11-16
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function logBoss(){
    	return $this->hasMany('LogBoss','user_id');
    }

    /**
     * 关联邮箱信息
     * @Author Foggy
     * @Date   2018-12-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function emailConfig(){
        return $this->hasOne('UsersEmailConfig', 'userid');
    }
}