<?php
namespace app\index\controller;
use think\Request;
use think\Db;
use app\common\model\Users as UsersModel;
use app\common\model\Company as CompanyModel;
use app\common\model\AuthRule as AuthRuleModel;
use app\common\model\AuthGroup as AuthGroupModel;
use app\common\model\Department as DepartmentModel;
use app\common\model\AuthGroupAccess as AuthGroupAccessModel;

/**
 * 领众超级管理员权限。
 */
class Superman extends Base{
	public $user;

	/**
	 * 构造方法
	 * @Author Foggy
	 * @Date   2018-12-18
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function __construct(){
		parent::__construct();
		$this->user = session('loginUser');
		if($this->user['auth_token'] !== 'bGluam9pbi4xMzE0KzA=' && $this->user['id'] !== 1){
			$this->error('非法请求');
			return false;
		}
	}

	/**
	 * 显示目前所有的可用的列表
	 * @Author Foggy
	 * @Date   2018-12-18
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(){
		$where = [];
		if($company = input('post.search_company')){
			$where[] = ['company', 'like', '%'.$company.'%'];
			$this->assign('search_company', $company);
		}
		$lists = CompanyModel::getCompanys($where);
		$this->assign('lists', $lists);
		$version = $this->version();
		$this->assign('versions', $version);
		return view();
	}

	/**
	 * 获取到版本信息
	 * @Author Foggy
	 * @Date   2018-12-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function version($id = 0){
		$VersionModel = new \app\common\model\Version;
		if($id > 0){
			return $VersionModel->find($id);
		}else{
			return $VersionModel->order('sort')->select();
		}
	}

	/**
	 * 停用账号
	 * @Author Foggy
	 * @Date   2018-11-14
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function ban(Request $request){
		if(request()->isPost()){
			$id = $request->id;
			$info = CompanyModel::get($id);
			$result = CompanyModel::update(['is_ban'=> 1], ['id'=>$id]);
			if($result > 0){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->error('操作方式错误');
		}
	}

	/**
	 * 启用账号
	 * @Author Foggy
	 * @Date   2018-11-14
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function active(Request $request){
		if(request()->isPost()){
			$id = $request->id;
			$result = CompanyModel::update(['is_ban'=> 0],['id'=>$id]);
			if($result > 0){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->error('操作方式错误');
		}
	}

	/**
	 * 添加一个公司
	 * @Author Foggy
	 * @Date   2018-12-18
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function create(){

	}

	/**
	 * 保存一个公司
	 * @Author Foggy
	 * @Date   2018-12-18
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function save(Request $request){
		if(request()->isPost()){
			$data = array_filter(input('post.'));
			Db::startTrans();
			$validate = $this->validate($data,'app\index\validate\Company.create');
			if(true !== $validate){
			    // 验证失败 输出错误信息
			    $this->error($validate);
			}
			$res1 = CompanyModel::create($data);
			$data['is_admin'] = 1;
			$data['nickname'] = $data['username'];
			$data['com_id'] = $res1->id;
			//默认创建一个初始化部门
			$res3 = DepartmentModel::create(['com_id'=> $res1->id, 'name'=> '办公室']);
			//默认初始化创建一个岗位
			//获取到目前的所有权限
			$rules = AuthRuleModel::where('status', 1)->column('id');
			if(count($rules) > 0){
				$rules = implode(',', $rules);
			}
			$res4 = AuthGroupModel::create(
				[
					'com_id'=> $res1->id, 
					'title'=> 'CEO',
					'rules'=> $rules,
					'department_id'=> $res3->id,
					'desc'=> 'CEO'
				]
			);
			$data['department_id'] = $res3->id;
			$data['group_id'] = $res4->id;
			//插入用户表
			$validate = $this->validate($data,'app\index\validate\User.super');
			if(true !== $validate){
			    // 验证失败 输出错误信息
			    $this->error($validate);
			}
			$res2 = UsersModel::create($data);
			$res5 = AuthGroupAccessModel::create(['uid'=> $res2->id, 'group_id'=> $res4->id]);
			if($res1->id > 0 && $res2->id > 0 && $res3->id > 0 && $res4->id > 0 && $res5 > 0){
				Db::commit();
				$this->success('操作成功');
			}else{
				Db::rollback();
				$this->error('操作失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 编辑一个公司
	 * @Author Foggy
	 * @Date   2018-12-18
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function edit(Request $request){
		if(request()->isPost()){
			$id = input('post.id');
			$info = CompanyModel::get($id);
			$this->success('获取数据成功', $_SERVER["HTTP_REFERER"], $info);
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 更新一个公司
	 * @Author Foggy
	 * @Date   2018-12-18
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function update(Request $request, CompanyModel $CompanyModel, UsersModel $UsersModel){
		if(request()->isPost()){
			$id = $request->id;
			$data = array_filter(input('post.'));
			if(trim($data['password']) == ''){
				unset($data['password']);
			}
			Db::startTrans();
			$res1= $CompanyModel->readonly('username')->allowField(true)->save($data, ['id'=> $id]);
			$res2 = $UsersModel->allowField(['username','password'])->save($data, ['com_id'=> $id, 'is_admin'=> 1]);
			if($res1 > 0 && $res2 > 0){
				Db::commit();
				$this->success('操作成功');
			}else{
				Db::rollback();
				$this->error('操作失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 删除一个公司
	 * @Author Foggy
	 * @Date   2018-12-18
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function delete(){

	}
}