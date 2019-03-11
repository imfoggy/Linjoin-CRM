<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Users as UsersModel;
use app\common\model\AuthGroup as AuthGroupModel;
use app\common\model\Department as DepartmentModel;

class Department extends Base{

	use \app\traits\controller\Mytraits;

	public $departmentArray;

	public function __construct(){
		parent::__construct();
		$lists = DepartmentModel::select()->toArray();
        $this->departmentArray = category_trees($lists);
        $this->user = session('loginUser');
	}
	
	/**
	 * 岗位列表
	 * @Author Foggy
	 * @Date   2018-10-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(Request $request){
		$departmentId = $request->department_id;
        $department_html = department_html($this->departmentArray);
        $departmentOption = department_option_html($this->departmentArray);
        $this->assign('departmentHtml', $department_html);
        $this->assign('departmentOption', $departmentOption);

        //获取到岗位
        $where = [];
        if($departmentId > 0){
        	$where[] = ['department_id', '=', $departmentId];
        }
        $positions = AuthGroupModel::getPositions($where);
        $array = category_trees($positions);
        $positions = group_html($array);
        $this->assign('positions', $positions);

        //按照部门分组获取到所有用户，用户添加领导人需要
        $departmentUsers = DepartmentModel::with('users')->select();
        $this->assign('departmentUsers', $departmentUsers);
		return view();
	}

	/**
	 * 编辑一个岗位
	 * @Author Foggy
	 * @Date   2018-11-10
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function edit(Request $request){
		if($request->departmentId > 0){
			$info = DepartmentModel::get($request->departmentId);
			if($info['id']){
				$this->success('数据获取成功', $_SERVER["HTTP_REFERER"], $info);
			}else{
				$this->error('数据获取失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 更新
	 * @Author Foggy
	 * @Date   2018-11-10
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function update($id, DepartmentModel $DepartmentModel){
		$result = 0;

		if(request()->isPost()){
			$data = array_filter(input('post.'));
			$result = $DepartmentModel->allowField(true)->where(['id'=> $id])->update($data);
		}

		if($result > 0){
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 保存岗位
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
			$result = DepartmentModel::create($post);
			if($result->id > 0){
				$this->success('操作成功', url('index'));
			}else{
				$this->error('操作失败');
			}
		}
	}

	/**
	 * 删除部门
	 * @Author Foggy
	 * @Date   2018-11-14
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function delete(Request $request){
		if(request()->isPost()){
			$departmentid = $request->departmentid;
			if($departmentid <= 0){
				$this->error('操作失败');
			}
			//递归检测这个职位下面以及下面的子类职位中是否有用户，若是有用户，则不允许删除
			$userTrees = department_users_check($departmentid);
			//当存在子部门，并且userTrees数组中的第一个元素不是当前部门的话
			if(count($userTrees) > 0 && $userTrees[0]!=$departmentid){
				$this->error('请先删除此部门下面的子部门');
			}

			$groups = AuthGroupModel::where('department_id', 'in', $userTrees)->select();
			if(count($groups) > 0){
				$this->error('请先删除此部门下面的职位');
			}

			$where [] = ['department_id', 'in', $userTrees];
			$users = UsersModel::getUsers($where);
			$users = $users->toArray();
			if($users['total'] > 0){
				$this->error('此部门中已经有人入职，无法删除！请先删除用户。');
			}
			$result = DepartmentModel::destroy($departmentid);
			if($result > 0){
				$this->success('操作成功', url('department/index'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}
}