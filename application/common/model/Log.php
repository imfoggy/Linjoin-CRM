<?php
namespace app\common\model;
use think\Model;

class Log extends Model{

	protected $pk = 'id';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';

	protected $insert = ['timestamp','date'];

	/**
	 * 自动补充当前时间戳
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	protected function setTimestampAttr(){
        return time();
    }

    protected function setDateAttr(){
    	return date('Y-m-d', time());
    }

    /**
     * 插入新日志
     * @Author Foggy
     * @Date   2018-10-09
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
	public function insert($data = []){

	}

	/**
	 * 相对关联用户表
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function userinfo(){
        return $this->belongsTo('Users','userid');
    }
}