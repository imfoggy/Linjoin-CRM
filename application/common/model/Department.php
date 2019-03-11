<?php
namespace app\common\model;
use think\Model;
/**
 * 部门表模型
 */
class Department extends Model{
	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
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
	/**
	 * 关联用户表，获取到这个部门下的所有用户
	 * @Author Foggy
	 * @Date   2018-11-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function users(){
		return $this->hasMany('Users', 'department_id');
	}
}