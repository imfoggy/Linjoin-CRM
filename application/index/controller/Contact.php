<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Customer as CustomerModel;
use app\common\model\CustomerContact as CustomerContactModel;

class Contact extends Base{
	use \app\traits\controller\Mytraits;

	public $config;

	protected $beforeActionList = [
		'customers'=> ['only'=> 'create,edit'],
		'country'=> ['only'=> 'create,edit'],
		'positions'=> ['only'=> 'create,edit,read']
	];

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
	 * 获取到客户信息
	 * @Author Foggy
	 * @Date   2018-11-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function customers(){
		//获取到供应商列表
		$customers = CustomerModel::select();
		$this->assign('customers', $customers);
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
        if($customerId > 0){
        	$where[] = ['customer_id', '=', $customerId];
        }
        $where[] = ['create_userid', '=', $this->user['id']];
		$lists = CustomerContactModel::getContacts($where);
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
	public function save(Request $request, CustomerContactModel $CustomerContactModel)
    {
        $data = input('post.');
        $data['create_userid'] = $this->user['id'];
        $result = $this->validate($data, 'app\index\validate\Contact.create');
        if(true !== $result){
            $this->error($result);
        }
        if($CustomerContactModel->allowField(true)->save($data)){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'新建了一条新的客户联系人，客户联系人ID为'.$CustomerContactModel->id.',客户姓名是['.$data['name'].']');
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
		$info = CustomerContactModel::getContactInfoById($id);
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

        $result = CustomerContactModel::update($post, ['id'=>$id]);
        if($result > 0){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了一条新的客户联系人，客户联系人ID为'.$CustomerContactModel->id);
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

        if(CustomerContactModel::destroy($str)){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除ID是'.$str.'的客户联系人');
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
		$info = CustomerContactModel::getContactInfoById($id);
		$this->assign('info', $info);
		return view();
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
}