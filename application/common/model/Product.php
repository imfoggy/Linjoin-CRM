<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class Product extends Model{
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
	 * 获取到产品列表
	 * @Author Foggy
	 * @Date   2018-11-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getProducts($where = [], $limit = 10){
		$result = self::with('sku')->where($where)->order('id desc')->paginate($limit);
		return $result;
	}

	/**
	 * 反向关联供应商
	 * @Author Foggy
	 * @Date   2018-11-17
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function supplier(){
		return $this->belongsTo('Supplier', 'supplier_id');
	}

	/**
	 * 关联创建人信息
	 * @Author Foggy
	 * @Date   2018-11-17
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function user(){
		return $this->belongsTo('Users', 'userid');
	}

	/**
	 * 关联sku信息
	 * @Author Foggy
	 * @Date   2018-11-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function sku(){
		return $this->hasMany('ProductSku', 'product_id');
	}

	/**
	 * 反向关联产品分类
	 * @Author Foggy
	 * @Date   2018-11-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function category(){
		return $this->belongsTo('ProductCategory', 'product_category_id');
	}

	/**
	 * 关联上传的文件
	 * @Author Foggy
	 * @Date   2018-12-05
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function files(){
		return $this->hasMany('Files', 'out_id')->where('from', '=', 'Product');
	}
}