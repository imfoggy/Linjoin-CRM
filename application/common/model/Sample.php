<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class Sample extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
	//新增的时候自动完成
	protected $insert = ['com_id'];
	// 定义全局的查询范围
    protected $globalScope = ['com'];

	/**
	 * 新增的时候自动增加com_id
	 * @Author Foggy
	 * @Date   2018-12-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	protected function setComIdAttr($value){
		return session('loginUser.com_id');
	}

	/**
	 * 全局查询条件
	 * @Author Foggy
	 * @Date   2018-12-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $query [description]
	 * @return [type]                   [description]
	 */
    public function scopeCom($query)
    {
        $query->where('com_id',session('loginUser.com_id'));
    }

	/**
	 * 获取器，对快递付款方式进行自动处理
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $value [description]
	 * @return [type]                   [description]
	 */
	public function getExpressPayTypeAttr($value){
		$status = ['寄方付', '收方付'];
        return $status[$value];
	}

	/**
	 * 获取到收样列表
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  array             $where [description]
	 * @param  integer           $limit [description]
	 * @return [type]                   [description]
	 */
	public static function getReceiveSamples($where=[], $limit=10){
		//type=1为收样
		//$where[] = ['type', '=', 1];
		$result = self::with('supplier,receiveUser')->where($where)->order('id desc')->paginate($limit);
		return $result;
	}

	/**
	 * 获取到指定的样品信息
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public static function getSampleInfo($id){
		return self::with('supplier,receiveUser')->get($id);
	}

	/**
	 * 反向关联供应商
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function supplier(){
		return $this->belongsTo('Supplier','supplier_id');
	}

	/**
	 * 获取到收样人信息
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function receiveUser(){
		return $this->belongsTo('Users', 'receive_user_id');
	}

	/**
	 * 获取到创建人信息
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function createUser(){
		return $this->belongsTo('Users', 'userid');
	}
}