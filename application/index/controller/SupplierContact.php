<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Supplier as SupplierModel;
use app\common\model\SupplierContact as SupplierContactModel;

class SupplierContact extends Base{

	use \app\traits\controller\Mytraits;

	public $config;

	protected $beforeActionList = [
		'suppliers'=> ['only'=> 'create,edit'],
		'positions'=> ['only'=> 'create,edit,read']
	];


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
	 * 获取到职位信息
	 * @Author Foggy
	 * @Date   2018-12-11
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function positions(){
		$positions = get_options('positions_config');
        $this->assign('positions', $positions);
	}
	
	/**
	 * 构造方法
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function __construct(){
		parent::__construct();
		$this->user = session('loginUser');
		$this->config = [
			'contactRole'=> get_options('customer_contact_role'),
			'sex'=> ['m'=> '先生', 'w'=> '女士']
		];

		$this->assign('_config', $this->config);
	}

	/**
	 * 联系人列表
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index($customerId = 0){
		$username = input('username', '');
        if($username){
            $where[] = ['name','like','%'.$username.'%'];
            $this->assign('username', $username);
        }
        if($supplierId > 0){
        	$where[] = ['supplier_id', '=', $supplierId];
        }
		$lists = SupplierContactModel::getContacts($where);
		$this->assign('lists', $lists);
		return view();
	}

	/**
	 * 新建联系人信息
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function create(){
		return view();
	}

	/**
	 * 保存联系人信息
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function save(Request $request, SupplierContactModel $SupplierContactModel)
    {
        $data = input('post.');
        $data['create_userid'] = $this->user['id'];
        $result = $this->validate($data, 'app\index\validate\SupplierContact.create');
        if(true !== $result){
            $this->error($result);
        }
        if($SupplierContactModel->allowField(true)->save($data)){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'增加了 ['.$data['name'].']的供应商联系人。');
            $this->success('操作成功', url('index'));
        }else{
            $this->error('操作失败');
        }
    }

	/**
	 * 编辑页面
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function edit($id){
		if(!$id){
			$this->error('信息不存在');
		}
		$info = SupplierContactModel::getContactInfoById($id);
		$this->assign('info', $info);
		return view();
	}

	/**
	 * 更新操作
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function update($id){
		if(!$id){
            $this->error('信息不存在');
        }
        $post = array_filter(input('post.'));

        $result = SupplierContactModel::update($post,['id'=> $id]);
        if($result > 0){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了ID是'.$id.'的供应商联系人。');
            return ['code'=> 1, 'msg'=> '操作成功'];
        }else{
            return ['code'=> 0, 'msg'=> '操作失败'];
        }
	}

	/**
	 * 删除操作
	 * @Author Foggy
	 * @Date   2018-10-24
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

        if(SupplierContactModel::destroy($str)){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了ID是'.$str.'的供应商联系人。');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
	}

	/**
	 * 联系人详情
	 * @Author Foggy
	 * @Date   2018-10-25
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function read($id){
		if(!$id){
			$this->error('信息不存在');
		}
		$info = SupplierContactModel::getContactInfoById($id);
		$this->assign('info', $info);
		return view();
	}
}