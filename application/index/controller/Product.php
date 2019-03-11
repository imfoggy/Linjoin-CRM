<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use app\common\model\Users as UsersModel;
use app\common\model\Product as ProductModel;
use app\common\model\Supplier as SupplierModel;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\ProductSpec as ProductSpecModel;
use app\common\model\ProductCategory as ProductCategoryModel;
use app\common\model\ProductSpecValue as ProductSpecValueModel;

class Product extends Base{

	use \app\traits\controller\Mytraits;

	public $config;

	protected $beforeActionList = [
		'suppliers'=> ['only'=> 'create,edit'],
		'categorys'=> ['only'=> 'create,edit']
	];

	/**
	 * 构造方法
	 * @Author Foggy
	 * @Date   2018-11-03
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function initialize(){
		$this->config = [
			'product_unit'=> get_options('product_unit'),
			'is_sample'=> ['y'=> '有', 'n'=> '无'],
			'is_sku'=> ['y'=> '有', 'n'=> '无'],
			'status'=> [
				1=> ['name'=> '已上架', 'color'=> 'green'], 
				2=> ['name'=> '已下架', 'color'=> 'red'],
			]
		];
		$this->user = session('loginUser');
		$this->assign('_config', $this->config);
	}

	/**
	 * 获取到供应商信息
	 * @Author Foggy
	 * @Date   2018-11-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function suppliers(){
		//获取到供应商列表
		$suppliers = SupplierModel::select();
		$this->assign('suppliers', $suppliers);
	}

	/**
	 * 获取到产品分类信息
	 * @Author Foggy
	 * @Date   2018-11-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function categorys(){
		//产品分类
		$category = ProductCategoryModel::select();
		$array = category_trees($category);
		$categoryHtml = category_html($array);
		$this->assign('categoryHtml', $categoryHtml);
	}

	/**
	 * 产品列表页
	 * @Author Foggy
	 * @Date   2018-11-03
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(){
		$where = [];
		$productName = input('product_name', '');
		$status = input('status', 1);
		$categoryid = input('categoryid','');
		$where[] = ['status', '=', $status];
		$this->assign('status', $status);
        if($productName){
            $where[] = ['product_name','like','%'.$productName.'%'];
            $this->assign('product_name', $productName);
        }
        if($categoryid > 0){
        	$where[] = ['product_category_id', '=', $categoryid];
        	$this->assign('categoryid', $categoryid);
        }
        $where[] = ['com_id', '=', $this->user['com_id']];
		$lists = ProductModel::getProducts($where);
		$this->assign('lists', $lists);
		//获取到左侧分类树状图
		//产品分类
		$category = ProductCategoryModel::select();
		$array = category_trees($category);
		$categoryTreeHtml = category_tree_html($array);
		$this->assign('categoryTreeHtml', $categoryTreeHtml);
		return view();
	}

	/**
	 * 产品添加页面
	 * @Author Foggy
	 * @Date   2018-11-03
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function create(){
		return view();
	}

	/**
	 * 保存
	 * @Author Foggy
	 * @Date   2018-11-17
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function save(Request $request, ProductModel $ProductModel, ProductSkuModel $ProductSkuModel){
		$data = input('post.');
		$data['product']['userid'] = $this->user['id'];
		$result = $this->validate($data['product'], 'app\index\validate\Product.create');
        if(true !== $result){
            $this->error($result);
        }

        Db::startTrans();
        if($res = $ProductModel->allowField(true)->save($data['product'])){
        	//若是启用了多规格
        	if($data['product']['is_sku'] == 'y'){
        		if(!$data['list']){
        			$this->error('请正确填写多规格属性');
        		}
        		$skus = combineDika($data['list']);
        		if(empty($skus)){
        			$this->error('请正确选择/填写规格值');
        		}

        		$product_id = $ProductModel->id;
        		foreach ($skus as $key => $value) {
        			$d[$key]['product_id'] = $product_id;
        			$attrs = array_keys($value,true);
        			$d[$key]['attributes'] = implode(':', $attrs);
        			$d[$key]['cost_price'] = $data['product_list'][$key+1]['cost_price'];
        			$d[$key]['procurement_price'] = $data['product_list'][$key+1]['procurement_price'];
        			$d[$key]['supplier_price'] = $data['product_list'][$key+1]['supplier_price'];
        			$d[$key]['sale_price'] = $data['product_list'][$key+1]['sale_price'];
        		}

        		$res1 = $ProductSkuModel->saveAll($d);
        	}else{
        		$product_id = $ProductModel->id;
        		$d['product_id'] = $product_id;
        		$d['cost_price'] = $data['product']['cost_price'];
        		$d['procurement_price'] = $data['product']['procurement_price'];
        		$d['supplier_price'] = $data['product']['supplier_price'];
        		$d['sale_price'] = $data['product']['sale_price'];
        		$res1 = $ProductSkuModel->save($d);
        	}
            
            if($res > 0 && $res1 > 0){
            	Db::commit();
            	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'添加了产品 ['.$data['product']['product_name'].']');
            	$this->success('操作成功', url('index'));
            }else{
            	Db::rollback();
            	$this->error('操作失败');
            }
        }else{
            $this->error('操作失败');
        }
	}

	/**
	 * 更新操作
	 * @Author Foggy
	 * @Date   2018-11-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id      [description]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function update($id,Request $request, ProductModel $ProductModel){
		$data = input('post.');
		$data['product']['userid'] = $this->user['id'];
		$result = $this->validate($data['product'], 'app\index\validate\Product.create');
        if(true !== $result){
            $this->error($result);
        }
        $res = ProductModel::update($data['product'], ['id'=> $id]);
        if($res > 0){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了ID为'.$id.'的产品');
        	$this->success('操作成功',url('index'));
        }else{
        	$this->error('操作失败');
        }
	}

	/**
	 * 产品详情页面
	 * @Author Foggy
	 * @Date   2018-11-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id      [description]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function read($id, Request $request){
		$id = $request->id;
		$info = ProductModel::with('supplier,sku,category,files')->get($id);
		if($info['is_sku'] == 'y' && $info['sku'][0]){
			foreach ($info['sku'] as $key => &$value) {
				$attributes = $value['attributes'];
				$array = explode(':', $attributes);
				$attrStr = '';
				foreach ($array as $k => $v) {
					$valueInfo = ProductSpecValueModel::get($v);
					$specInfo = ProductSpecModel::get(['id'=> $valueInfo['spec_id']]);
					$attrStr .= $specInfo['name'].'：'.$valueInfo['value'].'|';
				}
				$attrStr = trim($attrStr, '|');
				$value['attrStr'] = $attrStr;
			}
		}
		if($info['files']){
			foreach ($info['files'] as $key => &$value) {
				$value['username'] = UsersModel::getUsernameById($value['userid']);
			}
		}
		$this->assign('info', $info);
		return view();
	}

	/**
	 * 产品编辑
	 * @Author Foggy
	 * @Date   2018-11-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id      [description]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function edit($id, Request $request){
		$id = $request->id;
		$info = ProductModel::with('supplier,sku,category')->get($id);
		$this->assign('info', $info);
		return view();
	}

	/**
	 * 删除
	 * @Author Foggy
	 * @Date   2018-11-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function delete($id){
		if(!$id){
            $this->error('操作失败，请至少选择一条信息');
        }

        $str = $id;
        $idArray = $id;
        //判断id是单个还是多个
        if(is_array($id)){
            $str = implode(',', $id);
            $idArray = $id;
        }

        if(ProductModel::destroy($str)){
            ProductSkuModel::destroy(['product_id'=> $id]);
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除ID为'.$str.'的产品');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
	}

	/**
	 * 下架/上架商品
	 * @Author Foggy
	 * @Date   2018-11-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function productstatus($id,$status, Request $request){
		$id = $request->id;
		$status = $request->status;
		$res = ProductModel::where(['id'=> $id])->update(['status'=> $status]);
		ProductSkuModel::where(['product_id'=> $id])->update(['status'=> $status]);
		if($res > 0){
			self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更改ID为'.$id.'的产品状态为'.$this->config['status'][$status]['name']);
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 产品分类管理
	 * @Author Foggy
	 * @Date   2018-11-07
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function category(Request $request, ProductCategoryModel $ProductCategoryModel){
		$action = $request->action ? $request->action : 'index';
		switch ($action) {
			case 'save':
				$data = input('post.');
				$data['com_id'] = $this->user['com_id'];
				if($ProductCategoryModel->allowField(true)->save($data)){
					$this->success('操作成功', url('index'));
				}else{
					$this->error('操作失败');
				}
				break;

			case 'edit':
				$id = $request->id;
				$info = $ProductCategoryModel::get($id);
				return $info;
				break;

			case 'update':
				$id = $request->id;
				if($id <= 0){
					$this->error('id参数不正确');
				}
				$post = input('post.');
				$result = $ProductCategoryModel->save($post, ['id'=> $id]);
				if($result > 0){
					$this->success('操作成功', url('category'));
				}else{
					$this->error('操作失败');
				}
				break;

			//获取指定分类下的sku属性和值
			case 'attributes':
				$id = $request->id;
				$info = $ProductCategoryModel::with('spec')->get($id);
				if($info['spec']){
					foreach ($info['spec'] as $key => &$value) {
						$value['valueArray'] = ProductSpecModel::getSpecInfoById($value['id']);
					}
				}
				return $info ? $info : [];
				break;

			case 'delete':
				
				break;
			
			default:
				$category = ProductCategoryModel::select()->toArray();
				$array = category_trees($category);
				$categoryHtml = category_html($array);
				$this->assign('categoryHtml', $categoryHtml);
				$listHtml = category_list_html($array);
				$this->assign('listHtml', $listHtml);
		        return view('product_category');
				break;
		}
	}
}