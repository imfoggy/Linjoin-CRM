<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class AuthGroup extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
	//新增的时候自动完成
	// protected $insert = ['com_id'];
	// 定义全局的查询范围
    protected $globalScope = ['com'];

	/**
	 * 新增的时候自动增加com_id
	 * @Author Foggy
	 * @Date   2018-12-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	// protected function setComIdAttr($value){
	// 	return session('loginUser.com_id');
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

	//获取到岗位列表
	public static function getPositions($where=[]){
		return self::with('department')->withCount('users')->where($where)->select();
	}

	/**
	 * 反向关联部门表
	 * @Author Foggy
	 * @Date   2018-11-10
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function department(){
		return $this->belongsTo('Department','department_id');
	}

	/**
	 * 关联用户
	 * @Author Foggy
	 * @Date   2018-11-10
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function users(){
		return $this->hasMany('Users', 'group_id');
	}
}