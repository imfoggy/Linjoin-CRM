<?php
namespace app\common\model;
use think\Model;

class Version extends Model{
	protected $pk = 'id';
	// 设置json类型字段
	protected $json = ['regular'];
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;
}