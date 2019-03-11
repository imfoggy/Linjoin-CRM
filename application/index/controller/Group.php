<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Users as UsersModel;
use app\common\model\AuthGroup as AuthGroupModel;

class Group extends Base{

	use \app\traits\controller\Mytraits;

	public function __construct(){
		parent::__construct();
		$this->user = session('loginUser');
	}

	/**
	 * 获取到岗位信息
	 * @Author Foggy
	 * @Date   2018-11-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function info($id, Request $request){
		$info = AuthGroupModel::with('department')->get($id);
		if($info){
			$this->success('获取成功', $_SERVER["HTTP_REFERER"], $info);
		}else{
			$this->error('获取失败');
		}
	}

	/**
	 * 获取到职位列表
	 * @Author Foggy
	 * @Date   2018-11-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function position(Request $request){
		$where = [];
		$departmentId = $request->department_id;
		if($departmentId > 0){
			$where[] = ['department_id', '=', $departmentId];
		}

		$lists = AuthGroupModel::where($where)->select();
		// return $lists;
		$array = category_trees($lists);
		$options = group_option_html($array);
		return $options;
	}

	/**
	 * 编辑职位
	 * @Author Foggy
	 * @Date   2018-11-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function edit(Request $request){
		$id = input('post.id');
		$groupinfo = AuthGroupModel::get($id);
		$lists = AuthGroupModel::where(['department_id'=> $groupinfo['department_id']])->where('id','<>',$id)->select();
		$array = category_trees($lists);
		$options = group_option_html($array);
		$data['positions'] = $options;
		$data['data'] = AuthGroupModel::get($request->id);
		return $data;
	}

	/**
	 * 保存职位
	 * @Author Foggy
	 * @Date   2018-11-10
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function save(Request $request){
		if(request()->isPost()){
			$post = input('post.');
			$post['com_id'] = $this->user['com_id'];
			$post['rules'] = '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50';
			$result = AuthGroupModel::create($post);
			if($result->id > 0){
				$this->success('操作成功', url('department/index'));
			}else{
				$this->error('操作失败');
			}
		}
	}

	/**
	 * 职位更新
	 * @Author Foggy
	 * @Date   2018-11-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function update(Request $request, AuthGroupModel $AuthGroupModel){
		if(request()->isPost()){
			$data = input('post.');
			$result = $AuthGroupModel->allowField(true)->save($data, ['id'=> $request->id]);
			if($result > 0){
				$this->success('操作成功',url('department/index'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 删除职位
	 * @Author Foggy
	 * @Date   2018-11-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function delete(Request $request){
		if(request()->isPost()){
			$groupid = $request->groupid;
			if($groupid <= 0){
				$this->error('操作失败');
			}
			//递归检测这个职位下面以及下面的子类职位中是否有用户，若是有用户，则不允许删除
			$userTrees = group_users_check($groupid);
			$where [] = ['group_id', 'in', $userTrees];
			$users = UsersModel::getUsers($where);
			$users = $users->toArray();
			if($users['total'] > 0){
				$this->error('此岗位或是下级岗位中已经有人入职，无法删除！请先删除用户。');
			}
			$result = AuthGroupModel::destroy($groupid);
			if($result > 0){
				$this->success('操作成功', url('department/index'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->error('操作失败');
		}
	}
}