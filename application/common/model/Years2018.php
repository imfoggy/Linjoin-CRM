<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class Years2018 extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 设置当前模型对应的完整数据表名称
    protected $table = 'linjoin_crm_2018_years';

    // 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
}