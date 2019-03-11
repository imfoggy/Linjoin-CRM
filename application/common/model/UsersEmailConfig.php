<?php
namespace app\common\model;
use think\Model;

class UsersEmailConfig extends Model{
	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
}