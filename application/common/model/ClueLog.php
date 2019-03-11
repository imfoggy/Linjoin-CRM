<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;
use app\common\model\Clue as ClueModel;

class ClueLog extends Model{

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
		//线索下的clue_log_number字段计数+1
		self::event('after_insert', function($log, ClueModel $ClueModel){
			$ClueModel->where(['id'=> $log['clue_id']])->setInc('clue_log_number');
		});
		#2.删除沟通日志后的模型事件处理
		//线索下的clue_log_number字段计数-1
		self::event('after_delete', function($log, ClueModel $ClueModel){
			try {
				//防止自减成负数后报错，所以try一下
				$ClueModel->where(['id'=> $log['clue_id']])->setDec('clue_log_number');
			} catch (\Exception $e) {}
		});
	}

	public function getFollowUpTxtAttr($value, $data){
		$arr = get_options('follow_up');
		return $arr[$data['follow_up_id']];
	}

	/**
	 * 关联线索表
	 * @Author Foggy
	 * @Date   2018-10-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function clue(){
		return $this->belongsTo('Clue','clue_id');
	}

	/**
	 * 获取到线索负责人信息
	 * @Author Foggy
	 * @Date   2018-10-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function clueLogUser(){
		return $this->belongsTo('Users', 'userid');
	}

	/**
	 * 线索日志关联的附件
	 * @Author Foggy
	 * @Date   2018-10-16
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function file(){
		return $this->hasOne('ClueLogFiles','clue_log_id');
	}
}