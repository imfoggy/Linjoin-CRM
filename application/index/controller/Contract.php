<?php
namespace app\index\controller;
use think\Db;
use think\Request;
use app\common\model\Users as UsersModel;
use app\common\model\Product as ProductModel;
use app\common\model\Customer as CustomerModel;
use app\common\model\Contract as ContractModel;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\ProductSpec as ProductSpecModel;
use app\common\model\ProductCategory as ProductCategoryModel;
use app\common\model\ContractProducts as ContractProductsModel;
use app\common\model\ProductSpecValue as ProductSpecValueModel;
use app\common\model\ContractApproveLog as ContractApproveLogModel;

class Contract extends Base{
	use \app\traits\controller\Mytraits;

	/**
	 * 前置操作
	 * @var [type]
	 */
	protected $beforeActionList = [
		'users'=> ['only'=> 'create,edit'],
		'customers'=> ['only'=> 'create,edit'],
		'country'=> ['only'=> 'create,edit'],
		'getProductSku'=> ['only'=> 'create,edit'],
		'categorys'=> ['only'=> 'create,edit']
	];

	/**
	 * 构造方法
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function __construct(){
		parent::__construct();
		$this->user = session('loginUser');
		$this->config = [
			'product_unit'=> get_options('product_unit'),
			'contract_status'=> [
				1=> ['text'=> '待审核', 'color'=> '#F5CA00'],
				2=> ['text'=> '已通过', 'color'=> '#5ACD61'],
				3=> ['text'=> '已拒绝', 'color'=> '#f70505'],
			],
			'payment'=> [1=> 'T/T', 2=> 'L/C', 3=> 'D/A', 4=> 'D/P'],
			'currency_config'=> get_options('currency_config'),
			'contract_type_text'=> [1=> '采购合同', 2=> '销售合同']
		];
		$this->assign('_config', $this->config);
	}

	/**
	 * 获取到列表数据
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(Request $request, ContractModel $ContractModel){
		$where = [];
        $name = input('name', '');
        $by = input('by', '');
        $type = input('type');
        if($type){
        	$where[] = ['type', '=', $type];
        }
        if($name){
            $where[] = ['name','like','%'.$name.'%'];
            $where[] = ['type', '=', $type];
            $this->assign('search_name', $name);
        }
        $status = input('status', '');
        if($status){
            $where[] = ['status','=',$status];
            $where[] = ['type', '=', $type];
            $this->assign('search_status', $status);
        }

        
        if($by == 'pending_contract'){
        	$where[] = ['approvers_userid', '=', $this->user['id']];
        	$where[] = ['status', '=', 1];
        }
        //dump($where);exit;
		$list = $ContractModel->getContracts($where);
		$this->assign('list', $list);
		$this->assign('type', $type);
		return view();
	}

	/**
	 * 添加页面
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function create(){
		$type = input('type', 1);
		$this->assign('type', $type);
		return view();
	}

	/**
	 * 获取到用户列表
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	protected function users(){
		$users = UsersModel::scope('activeUser')->select();
		$this->assign('users', $users);
	}

	/**
	 * 获取到客户列表
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	protected function customers(){
		$customers = CustomerModel::select();
		$this->assign('customers', $customers);
	}

	/**
	 * 获取到国家列表
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function country(){
		$countries = db('countries')->select();
		$this->assign('countries', $countries);
	}

	/**	
	 * 获取到商品的多规格
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  array             $where   [description]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function getProductSku($where = []){
		$request = new Request();
		//根据关联条件来查询当前模型对象数据，查询出关联表中product_category_id为1的.
		if($request->categoryid > 0){
			$where[] = ['product_category_id', '=', $request->categoryid];
		}
		if($request->searchProductName){
			$where[] = ['product_name', 'like', '%'.$request->searchProductName.'%'];
		}
		// $lists = ProductSkuModel::where('com_id', $this->user['com_id'])->hasWhere('product', $where)->with('product')->select();
		//先查询sku
		$lists = ProductSkuModel::where(['status'=> 1])->select();
		foreach ($lists as $key => &$value) {
			$value['product'] = ProductModel::get($value['product_id']);
			$value['product']['category_name'] = ProductCategoryModel::where(['id'=> $value['product']['product_category_id']])->value('catname');
			$value['product']['unitText'] = $this->config['product_unit'][$value['product']['unit_id']];
			$attributes = $value['attributes'];
			$attrStr = '';
			if($attributes){
				$array = explode(':', $attributes);
				foreach ($array as $k => $v) {
					$valueInfo = ProductSpecValueModel::get($v);
					$specInfo = ProductSpecModel::get(['id'=> $valueInfo['spec_id']]);
					$attrStr .= $specInfo['name'].'：'.$valueInfo['value'].'|';
				}
				$attrStr = trim($attrStr, '|');
			}else{
				$attrStr = '-';
			}
			$value['attrStr'] = $attrStr;
		}
		if(request()->isAjax()){
			return $lists;
		}
		//dump($lists);
		$this->assign('products', $lists);
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
	 * 保存合同信息
	 * @Author Foggy
	 * @Date   2018-11-23
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function save(Request $request, ContractModel $ContractModel, ContractProductsModel $ContractProductsModel){
		$data = input('post.')['contract'];
		$products = input('post.')['product'];
		// if(!$products){
		// 	$this->error('请选择产品');
		// }
		$data['create_userid'] = $this->user['id'];
		$data['payment_desc'] = json_encode($data['payment'.$data['payment']]);
        $result = $this->validate($data, 'app\index\validate\Contract.create');
        if(true !== $result){
            $this->error($result);
        }

        Db::startTrans();
        #保存合同表基本信息
        $res1 = $ContractModel->allowField(true)->save($data);
        if($res1){
        	if(count($products) > 0){
        		foreach ($products as $key => $value) {
	        		$d[$key]['contract_id'] = $ContractModel->id;
	        		$d[$key]['userid'] = $this->user['id'];
	        		$d[$key]['skuid'] = $value['skuid'];
	        		$d[$key]['product_id'] = $value['product_id'];
	        		$d[$key]['sale_price'] = $value['sale_price'];
	        		$d[$key]['number'] = $value['number'];
	        		$d[$key]['total'] = $value['total'];
	        	}
	        	#插入合同产品表
	        	$res2 = $ContractProductsModel->saveAll($d);
        	}else{
        		$res2 = 1;
        	}

        	if($data['approvers_userid'] > 0){
        		#插入审批人记录表
        		$res3 = ContractApproveLogModel::create(['contract_id'=> $ContractModel->id, 'approve_user_id'=> $data['approvers_userid']]);
        	}else{
        		$res3 = 1;
        	}
        }
        if($res1 > 0 && $res2 > 0 && $res3 > 0){
        	Db::commit();
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'增加了一份合同，合同编号为'.$data['sn']);
            $type = ContractModel::where(['id'=> $id])->value('type');
            $this->success('操作成功', url('index', ['type'=> $type]));
        }else{
        	Db::rollback();
            $this->error('操作失败');
        }
	}

	/**
	 * 编辑信息
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function edit(Request $request){
		$id = $request->id;
		$info = ContractModel::getContractInfo($id);
		//$info['payment_desc'] = json_decode($info['payment_desc'], true);
		if($info['contract_products']){
			foreach ($info['contract_products'] as $key => &$value) {
				#获取到多规格信息
				$attributes = ProductSkuModel::where(['id'=> $value['skuid']])->value('attributes');
				$attrStr = '';
				if($attributes){
					$array = explode(':', $attributes);
					foreach ($array as $k => $v) {
						$valueInfo = ProductSpecValueModel::get($v);
						$specInfo = ProductSpecModel::get(['id'=> $valueInfo['spec_id']]);
						$attrStr .= $specInfo['name'].'：'.$valueInfo['value'].'|';
					}
					$value['attrStr'] = trim($attrStr, '|');
				}else{
					$value['attrStr'] = '-';
				}

				#获取到单位信息
				$productInfo = ProductModel::get(['id'=> $value['product_id']]);
				$value['product_name'] = $productInfo['product_name'];
				$value['unitText'] = $this->config['product_unit'][$productInfo['unit_id']];
			}
		}
		$this->assign('info', $info);
		return view();
	}

	/**
	 * 更新
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function update(Request $request, ContractModel $ContractModel, ContractApproveLogModel $ContractApproveLogModel, ContractProductsModel $ContractProductsModel){
		$id = $request->id;
		$data = input('post.');
		$dcontract = $data['contract'];
		$dproducts = $data['product'];
		$dcontract['create_userid'] = $this->user['id'];
		$dcontract['payment_desc'] = json_encode($dcontract['payment'.$dcontract['payment']]);
		unset($dcontract['payment1']);
		unset($dcontract['payment2']);
		$result = $this->validate($dcontract, 'app\index\validate\Contract.create');
        if(true !== $result){
            $this->error($result);
        }

        Db::startTrans();
        #保存合同表基本信息
        $res1 = $ContractModel->allowField(true)->save($dcontract,['id'=> $id]);
        if($res1){
        	//先清空之前的所有产品信息
        	ContractProductsModel::destroy(['contract_id'=> $id],true);
        	if(count($dproducts) > 0){
        		//然后再重新添加
	        	foreach ($dproducts as $key => $value) {
	        		$d[$key]['contract_id'] = $id;
	        		$d[$key]['userid'] = $this->user['id'];
	        		$d[$key]['skuid'] = $value['skuid'];
	        		$d[$key]['product_id'] = $value['product_id'];
	        		$d[$key]['sale_price'] = $value['sale_price'];
	        		$d[$key]['number'] = $value['number'];
	        		$d[$key]['total'] = $value['total'];
	        	}
	        	#插入合同产品表
	        	$res2 = $ContractProductsModel->saveAll($d);
        	}else{
        		$res2 = 1;
        	}
        	
        	if($data['approvers_userid'] > 0){
        		#插入审批人记录表
        		$res3 = ContractApproveLogModel::create(['contract_id'=> $id, 'approve_user_id'=> $dcontract['approvers_userid']]);
        	}else{
        		$res3 = 1;
        	}
        }
        if($res1 > 0 && $res2 > 0 && $res3 > 0){
        	Db::commit();
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'修改了一份合同，合同编号为'.$data['sn']);
        	$type = ContractModel::where(['id'=> $id])->value('type');
            $this->success('操作成功', url('index', ['type'=> $type]));
        }else{
        	Db::rollback();
            $this->error('操作失败');
        }
	}

	/**
	 * 删除
	 * @Author Foggy
	 * @Date   2018-11-26
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

        if(ContractModel::destroy($str)){
            ContractProductsModel::destroy(['contract_id'=> $id]);
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除了ID为'.$str.'的合同');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
	}

	/**
	 * 合同审核功能
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function auditing(Request $request, ContractApproveLogModel $ContractApproveLogModel){
		$data = input('post.');

		if(!in_array($request->result, [1,2,3])){
			$this->error('审核操作错误');
		}

		$data['approve_user_id'] = $this->user['id'];
		//查询合同信息
		$info = ContractModel::get($request->contract_id);
		if($info['approvers_userid'] != $this->user['id']){
			$this->error('您无权审批此合同');
		}
		Db::startTrans();
		$res1 = $ContractApproveLogModel->create($data);
		$res2 = ContractModel::where(['id'=> $request->contract_id])->update(['status'=> $request->result]);
		if($res1 > 0 && $res2 > 0){
			Db::commit();
			self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'审核了ID为'.$request->contract_id.',状态修改为'.$this->config['contract_status'][$request->result]['text']);
			$this->success('操作成功');
		}else{
			Db::rollback();
			$this->error('操作失败');
		}
	}

	/**
	 * 合同详情页面
	 * @Author Foggy
	 * @Date   2018-11-28
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function read(Request $request){
		$id = $request->id;
		$info = ContractModel::getContractInfo($id);
		$this->assign('info', $info);
		//dump($info);
		return view();
	}
}