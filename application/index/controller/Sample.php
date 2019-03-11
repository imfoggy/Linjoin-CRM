<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Users as UsersModel;
use app\common\model\Sample as SampleModel;
use app\common\model\Supplier as SupplierModel;
use app\common\model\SendSample as SendSampleModel;

/**
 * 收发样管理
 */
class Sample extends Base{

	use \app\traits\controller\Mytraits;

	protected $beforeActionList = [
		'weightUnit'=> ['only'=> 'receive,send']
	];

	public function __construct(){
        parent::__construct();
        $this->user = session('loginUser');
        $this->weightUnit();
    }

    /**
     * 获取到重量单位
     * @Author Foggy
     * @Date   2018-12-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function weightUnit(){
    	$weightUnit = get_options('weight_config');
        $this->assign('weightUnit', $weightUnit);
    }

	/**
	 * 收样管理
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function receive(Request $request, SampleModel $SampleModel){
		$action = $request->action ? $request->action : 'index';
		switch ($action) {
			case 'create':
				//获取到供应商列表
				$suppliers = SupplierModel::select();
				$this->assign('suppliers', $suppliers);
				//获取到员工列表
				$users = UsersModel::scope('activeUser')->select();
				$this->assign('users', $users);
				return view('receive_create');
				break;

			case 'save':
				$data = input('post.');
		        $data['userid'] = $this->user['id'];
		        $result = $this->validate($data, 'app\index\validate\Sample.create');
		        if(true !== $result){
		            $this->error($result);
		        }
		        if($SampleModel->allowField(true)->save($data)){
		        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'增加了 ['.$data['class_name'].']的收样。');
		            $this->success('操作成功', url('receive',['action'=> 'index']));
		        }else{
		            $this->error('操作失败');
		        }
				break;

			case 'edit':
				$id = $request->id;
				$info = SampleModel::getSampleInfo($id);
				$this->assign('info', $info);
				//获取到供应商列表
				$suppliers = SupplierModel::select();
				$this->assign('suppliers', $suppliers);
				//获取到员工列表
				$users = UsersModel::scope('activeUser')->select();
				$this->assign('users', $users);
				return view('receive_edit');
				break;

			case 'update':
				$id = $request->id;
				$data = array_filter(input('post.'));
				$result = $this->validate($data, 'app\index\validate\Sample.edit');
		        if(true !== $result){
		            $this->error($result);
		        }
		        if($SampleModel->save($data,['id'=>$id])){
		        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了ID是'.$id.'的收样。');
		            $this->success('操作成功', url('receive',['action'=> 'index']));
		        }else{
		            $this->error('操作失败');
		        }
				break;

			case 'delete':
				$id = $request->id;
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

		        if(SampleModel::destroy($str)){
		        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除了ID是'.$str.'的收样。');
		            $this->success('操作成功');
		        }else{
		            $this->error('操作失败');
		        }
				break;
			
			default:
				$where = [];
		        $className = input('class_name', '');
		        if($className){
		            $where[] = ['class_name','like','%'.$className.'%'];
		            $this->assign('className', $className);
		        }
				$lists = SampleModel::getReceiveSamples($where);
				$this->assign('lists', $lists);
				return view();
				break;
		}
	}

	/**
	 * 发样管理
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function send(Request $request, SendSampleModel $SendSampleModel){
		$action = $request->action ? $request->action : 'index';
		switch ($action) {
			case 'create':
				//获取到员工列表
				$users = UsersModel::scope('activeUser')->select();
				$this->assign('users', $users);
				return view('send_create');
				break;

			case 'save':
				$data = input('post.');
		        $data['userid'] = $this->user['id'];
		        $result = $this->validate($data, 'app\index\validate\SendSample.create');
		        if(true !== $result){
		            $this->error($result);
		        }
		        if($SendSampleModel->allowField(true)->save($data)){
		        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'增加了['.$data['class_name'].']的发样。');
		            $this->success('操作成功', url('send',['action'=> 'index']));
		        }else{
		            $this->error('操作失败');
		        }
				break;

			case 'edit':
				$id = $request->id;
				$info = SendSampleModel::getSampleInfo($id);
				$this->assign('info', $info);
				//获取到员工列表
				$users = UsersModel::scope('activeUser')->select();
				$this->assign('users', $users);
				return view('send_edit');
				break;

			case 'update':	
				$id = $request->id;
				$data = array_filter(input('post.'));
				$result = $this->validate($data, 'app\index\validate\SendSample.edit');
		        if(true !== $result){
		            $this->error($result);
		        }
		        if($SendSampleModel->save($data,['id'=> $id])){
		        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了ID是'.$id.'的发样。');
		            $this->success('操作成功', url('send',['action'=> 'index']));
		        }else{
		            $this->error('操作失败');
		        }
				break;

			case 'delete':
				$id = $request->id;
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

		        if(SendSampleModel::destroy($str)){
		        	self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除了ID是'.$str.'的发样。');
		            $this->success('操作成功');
		        }else{
		            $this->error('操作失败');
		        }
				break;
			
			default:
				$where = [];
		        $className = input('class_name', '');
		        if($className){
		            $where[] = ['class_name','like','%'.$className.'%'];
		            $this->assign('className', $className);
		        }
				$lists = SendSampleModel::getSendSamples($where);
				$this->assign('lists', $lists);
				return view();
				break;
		}
	}
}