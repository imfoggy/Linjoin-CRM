<?php
namespace app\common\model;
use think\Model;

class LogBoss extends Model{
	public $pk = 'id';

	/**
	 * 获取到员工信息
	 * @Author Foggy
	 * @Date   2018-11-28
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function employee(){
		return $this->belongsTo('Users', 'user_id');
	}

	/**
	 * 获取到老板信息
	 * @Author Foggy
	 * @Date   2018-11-28
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function boss(){
		return $this->belongsTo('Users', 'boss_user_id');
	}
}