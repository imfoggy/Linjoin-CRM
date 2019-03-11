<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class ContractProducts extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
}