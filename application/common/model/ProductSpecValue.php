<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class ProductSpecValue extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';

	/**
	 * 关联spec信息
	 * @Author Foggy
	 * @Date   2018-10-30
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function spec(){
		return $this->belongsTo('ProductSpec', 'spec_id');
	}
}