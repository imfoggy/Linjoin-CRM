<?php
namespace app\common\model;
use think\Model;

class CustomerLogFiles extends Model{
	protected $pk = 'id';

	/**
	 * 关联客户沟通记录表
	 * @Author Foggy
	 * @Date   2018-10-16
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function customerLog(){
		return $this->belongsTo('CustomerLog', 'customer_log_id');
	}
}