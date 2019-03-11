<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;
use app\common\model\ProductCategory as ProductCategoryModel;
use app\common\model\Users as UsersModel;

class Supplier extends Model{
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
	 * 获取到供应商列表
	 * @Author Foggy
	 * @Date   2018-11-02
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getSuppliers($where=[], $limit = 10){
		$result = self::where($where)->order('id desc')->paginate($limit);

		return $result;
	}

	/**
	 * 根据供应商id来获取到供应商信息
	 * @Author Foggy
	 * @Date   2018-11-02
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getSupplierInfoById($id){
		$info = self::with('getCreateUser,products,files,contacts')->get($id);
		if($info['products'][0]){
			foreach ($info['products'] as $key => &$value) {
				$value['category_name'] = ProductCategoryModel::where(['id'=> $value['product_category_id']])->value('catname');
			}
		}

		if($info['files']){
			foreach ($info['files'] as $key => &$value) {
				$value['username'] = UsersModel::getUsernameById($value['userid']);
			}
		}

		return $info;
	}

	/**
	 * 关联的供应商联系人
	 * @Author Foggy
	 * @Date   2018-10-31
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function contacts(){
		return $this->hasMany('SupplierContact', 'supplier_id');
	}

	/**
	 * 关联到创始人信息
	 * @Author Foggy
	 * @Date   2018-11-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getCreateUser(){
		return $this->belongsTo('Users', 'create_userid');
	}

	/**
	 * 关联产品表的基本信息
	 * @Author Foggy
	 * @Date   2018-11-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function products(){
		return $this->hasMany('Product','supplier_id');
	}

	/**
	 * 远程一对多，查询某个供应商下所有的sku信息
	 * @Author Foggy
	 * @Date   2018-10-31
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function sku(){
		return $this->hasManyThrough('ProductSku', 'Product', 'supplier_id', 'product_id');
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
		return $this->hasMany('Files', 'out_id')->where('from', '=', 'Supplier');
	}
}