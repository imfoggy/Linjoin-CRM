<?php
namespace app\common\model;
use think\Model;

class ClueLogFiles extends Model{
	protected $pk = 'id';

	/**
	 * 关联线索沟通记录表
	 * @Author Foggy
	 * @Date   2018-10-16
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function clueLog(){
		return $this->belongsTo('ClueLog', 'clue_log_id');
	}
}