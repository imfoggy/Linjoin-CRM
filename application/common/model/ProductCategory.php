<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class ProductCategory extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
	//新增的时候自动完成
	// protected $insert = ['com_id'];
	// 定义全局的查询范围
    protected $globalScope = ['com'];

	/**
	 * 新增的时候自动增加com_id
	 * @Author Foggy
	 * @Date   2018-12-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	// protected function setComIdAttr($value){
	// 	return session('loginUser.com_id');
	// }

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
	 * 获取到关联的当前分类下的所有规格分类
	 * @Author Foggy
	 * @Date   2018-11-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function spec(){
		return $this->hasMany('ProductSpec', 'product_category_id');
	}

	/**
	 * 远程一对多，获取到产品分类下的属性和值
	 * @Author Foggy
	 * @Date   2018-11-17
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function specValue(){
        return $this->hasManyThrough('ProductSpecValue','ProductSpec', 'product_category_id', 'spec_id');
    }
}