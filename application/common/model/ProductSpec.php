<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class ProductSpec extends Model{
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
	 * 根据id获取到规格详情
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function getSpecInfoById($id){
		return self::with('values')->get($id);
	}

	/**
	 * 获取到规格列表
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getSpecs($categoryId = 0, $limit = 10){
		$where = [];

		if($categoryId > 0){
			$where[] = ['category_id', '=', $categoryId];
		}

		$result = self::with('values')->where($where)->order('id desc')->paginate($limit)->each(function($item, $key){
			// $item->create_username = db('users')->where(['id'=> $item->create_userid])->value('username');
			// $item->principal_username = db('users')->where(['id'=> $item->userid])->value('username');
		});

		return $result;
	}

	/**
	 * 关联规格值
	 * @Author Foggy
	 * @Date   2018-10-30
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function values(){
		return $this->hasMany('ProductSpecValue', 'spec_id');
	}

	/**
	 * 定义相对的关联，关联当前规格的产品分类
	 * @Author Foggy
	 * @Date   2018-11-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function category(){
		return $this->belongsTo('ProductCategory', 'product_category_id');
	}
}