<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Supplier as SupplierModel;

class Supplier extends Base{

	use \app\traits\controller\Mytraits;

	public $config;
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
			'property'=> get_options('customer_company_property'),
			'is_sample'=> ['y'=> '有', 'n'=> '无'],
			'is_sku'=> ['y'=> '有', 'n'=> '无'],
			'status'=> [
				1=> ['name'=> '已上架', 'color'=> 'green'], 
				2=> ['name'=> '已下架', 'color'=> 'red'],
			],
			'position'=> get_options('positions_config'),
			'customer_contact_role'=> get_options('customer_contact_role')
		];

		$this->assign('_config', $this->config);
	}

	/**
	 * 供应商列表
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index($supplierId = 0){
		$username = input('username', '');
        if($username){
            $where[] = ['name','like','%'.$username.'%'];
            $this->assign('username', $username);
        }
		$lists = SupplierModel::getSuppliers($where);
		$this->assign('lists', $lists);
		return view();
	}

	/**
	 * 新建供应商信息
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function create(){
		$sn = $this->cmsn();
		$this->assign('sn', $sn);
		return view();
	}

	/**
     * 生成供应商编号
     * @Author Foggy
     * @Date   2018-12-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function cmsn(){
        //自动生成客户编号
        $prefix = 'SR';
        $date = date('Ymd',time());
        $random = mt_rand(10000,99999);
        $sn = $prefix.$date.$random;
        return $sn;
    }

	/**
	 * 保存供应商信息
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function save(Request $request, SupplierModel $SupplierModel)
    {
        $data = input('post.');
        $data['create_userid'] = $this->user['id'];
        $result = $this->validate($data, 'app\index\validate\Supplier.create');
        if(true !== $result){
            $this->error($result);
        }
        if($SupplierModel->allowField(true)->save($data)){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'增加了['.$data['name'].']的供应商。');
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
		$info = SupplierModel::getSupplierInfoById($id);
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

        $result = SupplierModel::update($post, ['id'=>$id]);
        if($result > 0){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了ID为'.$id.'的供应商。');
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

        if(SupplierModel::destroy($str)){
        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了ID为'.$str.'的供应商。');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
	}

	/**
	 * 供应商详情
	 * @Author Foggy
	 * @Date   2018-10-25
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function read($id){
		$info = SupplierModel::getSupplierInfoById($id);
		$this->assign('info', $info);
		return view();
	}
}