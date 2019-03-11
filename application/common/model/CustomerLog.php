<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;
use app\common\model\Customer as CustomerModel;

class CustomerLog extends Model{

	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
	
	/**
	 * 模型事件
	 * @Author Foggy
	 * @Date   2018-11-22
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public static function init(){
		#1.新增沟通日志后的模型事件处理
		//客户下的customer_log_number字段计数+1
		self::event('after_insert', function($log, CustomerModel $CustomerModel){
			$CustomerModel->where(['id'=> $log['customer_id']])->setInc('customer_log_number');
		});
		#2.删除沟通日志后的模型事件处理
		//客户下的customer_log_number字段计数-1
		self::event('after_delete', function($log, CustomerModel $CustomerModel){
			try {
				//防止自减成负数后报错，所以try一下
				$CustomerModel->where(['id'=> $log['customer_id']])->setDec('customer_log_number');
			} catch (\Exception $e) {}
		});
	}
	
	public function getFollowUpTxtAttr($value, $data){
		$arr = get_options('follow_up');
		return $arr[$data['follow_up_id']];
	}

	/**
	 * 关联客户表
	 * @Author Foggy
	 * @Date   2018-10-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function customer(){
		return $this->belongsTo('Customer','customer_id');
	}

	/**
	 * 获取到客户负责人信息
	 * @Author Foggy
	 * @Date   2018-10-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function customerLogUser(){
		return $this->belongsTo('Users', 'userid');
	}

	/**
	 * 客户日志关联的附件
	 * @Author Foggy
	 * @Date   2018-10-16
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function file(){
		return $this->hasOne('CustomerLogFiles','customer_log_id');
	}
}