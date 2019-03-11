<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class ContractApproveLog extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';

	/**
	 * 关联合同信息
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function contract(){
		return $this->belongsTo('Contract', 'contract_id');
	}
}