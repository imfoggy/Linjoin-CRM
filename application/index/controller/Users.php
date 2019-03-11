<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Clue as ClueModel;
use app\common\model\Users as UsersModel;
use app\common\model\LogBoss as LogBossModel;
use app\common\model\Version as VersionModel;
use app\common\model\Company as CompanyModel;
use app\common\model\Customer as CustomerModel;
use app\common\model\AuthGroup as AuthGroupModel;
use app\common\model\Department as DepartmentModel;
use app\common\model\ClueUserRecorder as ClueUserRecorderModel;
use app\common\model\CustomerUserRecorder as CustomerUserRecorderModel;

class Users extends Base{

	use \app\traits\controller\Mytraits;

	public $config;
	public $departmentArray;
	public $groupArray;
	/**
	 * 构造方法
	 * @Author Foggy
	 * @Date   2018-11-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function __construct(){
		parent::__construct();
		$sex = ['m'=> '男', 'w'=> '女'];
		$this->assign('_sexText', $sex);
		$this->user = session('loginUser');

		//获取顶级部门optionHtml
		$department = DepartmentModel::select();
        $this->departmentArray = category_trees($department);
        $departmentOptionHtml = department_option_html($this->departmentArray);
        $this->assign('departmentOptionHtml', $departmentOptionHtml);

        //获取职位optionHtml
        $groups = AuthGroupModel::select();
		// return $lists;
		$this->groupArray = category_trees($groups);
		$groupOptionHtml = group_option_html($this->groupArray);
		$this->assign('groupOptionHtml', $groupOptionHtml);
	}

	/**
	 * 用户列表
	 * @Author Foggy
	 * @Date   2018-11-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(){
		$where = [];
		if($nickname = input('post.search_nickname')){
			$where[] = ['nickname', 'like', '%'.$nickname.'%'];
			$this->assign('search_nickname', $nickname);
		}
		$lists = UsersModel::getUsers($where, 10);
		$this->assign('lists', $lists);
		//按照部门分组获取到所有用户，用户添加领导人需要
        $departmentUsers = DepartmentModel::with('users')->select();
        //dump($departmentUsers);
        $this->assign('departmentUsers', $departmentUsers);
		return view();
	}

	/**
	 * 获取用户列表
	 * @Author Foggy
	 * @Date   2018-11-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getUsers(){
		$lists = UsersModel::getUsers();
		dump($lists);
	}

	/**
	 * 负责人页面
	 * @Author Foggy
	 * @Date   2018-11-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function principal(Request $request){
		$update = $request->isUpdate;
		if($update == 'update'){
			if(!$request->infoid || !$request->from || !$request->new_user_id){
				$this->error('参数错误');
			}

			$module = ['clue', 'customer'];

			if(!in_array($request->from, $module)){
				$this->error('模块参数错误');
			}

			if($request->from == 'clue'){
				$info = ClueModel::get($request->infoid);
				$res = ClueModel::setUserid($request->infoid, $request->new_user_id);

			}elseif($request->from == 'customer'){
				$info = CustomerModel::get($request->infoid);
				$res = CustomerModel::setUserid($request->infoid, $request->new_user_id);
			}

			if($res > 0){
				if($request->from == 'clue'){
					$res = ClueUserRecorderModel::create([
						'clue_id'=> $info['id'], 
						'old_user_id'=> $info['userid'],
						'new_user_id'=> $request->new_user_id,
						'content'=> '负责人变更'
					]);

				}elseif($request->from == 'customer'){
					$res = CustomerUserRecorderModel::create([
						'customer_id'=> $info['id'], 
						'old_user_id'=> $info['userid'],
						'new_user_id'=> $request->new_user_id,
						'content'=> '负责人变更'
					]);
				}
				$this->success('操作成功',url($request->from.'/'.'index'));
			}else{
				$this->error('操作失败');
			}
		}else{
			$where = [];
			$username = input('post.username', '');
	        if($username){
	            $where[] = ['username','like','%'.$username.'%'];
	            $this->assign('username', $username);
	        }
	        $departmentId = input('post.department_id', '');
	        if($departmentId){
	            $where[] = ['department_id','=', $departmentId];
	            $this->assign('department_id', $departmentId);
	        }else{
	        	$this->assign('department_id', "0");
	        }
			//获取到部门
			$lists = DepartmentModel::select()->toArray();
	        $array = category_trees($lists);
	        $departmentHtml = department_option_html($array);
	        $this->assign('departmentHtml', $departmentHtml);

	        //获取到用户
	        $users = UsersModel::getUsers($where);
	        $this->assign('users', $users);
	        $this->assign('from', $request->from);
	        $this->assign('infoid', $request->infoid);
			return view();
		}
	}

	/**
	 * 用户编辑功能
	 * @Author Foggy
	 * @Date   2018-11-14
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function edit(Request $request){
		$id = $request->id;
		$user = UsersModel::with('department,auth_group,log_boss')->get($id);
		$data['info'] = $user;
		$this->success('数据获取成功',$_SERVER['HTTP_REFERER'], $data);
	}

	/**
	 * 保存新员工
	 * @Author Foggy
	 * @Date   2018-11-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function save(Request $request, UsersModel $UsersModel, LogBossModel $LogBossModel){
		if(request()->isPost()){

			//检测用户所在的公司
			$company = CompanyModel::get(['id'=> $this->user['com_id']]);
			//检测版本信息
			$version = VersionModel::get(['id'=> $company['version_id']]);
			//获取到最大允许建立用户数量
			$allowMaxNumber = $version['regular']['users_number'];
			$userCount = UsersModel::where(['com_id'=> $this->user['com_id']])->count();
			if($userCount >= $allowMaxNumber){
				$this->error('您的当前版本无法添加更多用户，若需要，请联系管理员开通更高版本');
			}

			$data = input('post.');
			$data['com_id'] = $this->user['com_id'];

			$isHave = UsersModel::where(['username'=> $data['username']])->count();
			if($isHave > 0){
				$this->error('登录账号已经存在，请重新设置');
			}
			if($data['password'] != $data['confirmpassword']){
				$this->error('两次输入的密码不一致');
			}
			$validate1 = $this->validate($data,'app\index\validate\User.create');
			if(true !== $validate1){
			    // 验证失败 输出错误信息
			    $this->error($validate1);
			}
			$result = $UsersModel->allowField(true)->save($data);
			if($result > 0){
				//如果有上级领导的话，就插入上级领导表
				if($data['boss_user_id']){
					foreach ($data['boss_user_id'] as $key => $value) {
						$bossData[$key]['user_id'] = $UsersModel->id;
						$bossData[$key]['boss_user_id'] = $value;
					}

					$LogBossModel->saveAll($bossData);
				}
				//插入auth_group_access表
				db('auth_group_access')->insert(['uid'=> $UsersModel->id, 'group_id'=> $data['group_id']]);
				$this->success('操作成功');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 更新操作
	 * @Author Foggy
	 * @Date   2018-11-15
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function update(Request $request, UsersModel $UsersModel, LogBossModel $LogBossModel){
		if(request()->isPost()){
			$data = input('post.');
			if($data['password']){
				if($data['password'] != $data['confirmpassword']){
					$this->error('两次输入的密码不一致');
				}
			}else{
				unset($data['password']);
			}
			$validate1 = $this->validate($data,'app\index\validate\User.edit');
			if(true !== $validate1){
			    // 验证失败 输出错误信息
			    $this->error($validate1);
			}
			$result = $UsersModel->allowField(true)->save($data, ['id'=> $request->id]);
			if($result > 0){
					//删除之前保存的boss信息
					LogBossModel::destroy(['user_id'=> $request->id], true);
					if(count($data['boss_user_id']) > 0){
						foreach ($data['boss_user_id'] as $key => $value) {
							$bossData[$key]['user_id'] = $request->id;
							$bossData[$key]['boss_user_id'] = $value;
						}
						
						$LogBossModel->saveAll($bossData);
					}
				$this->success('操作成功');
			}
		}else{
			$this->error('请求方式错误');
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
			$info = UsersModel::get($id);

			if($info['id'] > 0 && $info['is_admin'] == 1){
				$this->error('不能停用总管理员账号');
			}
			$result = UsersModel::update(['status'=> 2], ['id'=>$id]);
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
			$result = UsersModel::update(['status'=> 1],['id'=>$id]);
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
	 * 获取到用户详情
	 * @Author Foggy
	 * @Date   2018-11-16
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id      [description]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function read($id, Request $request){
		$info = UsersModel::getUserInfoById($id);
		$this->assign('info', $info);
		return view();
	}

	/**
	 * 检测当前公司下面有多少员工了
	 * @Author Foggy
	 * @Date   2018-12-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function usersnumber(Request $request){
		if(request()->isPost()){
			//检测用户所在的公司
			$company = CompanyModel::get(['id'=> $this->user['com_id']]);
			//检测版本信息
			$version = VersionModel::get(['id'=> $company['version_id']]);
			//获取到最大允许建立用户数量
			$allowMaxNumber = $version['regular']['users_number'];
			$userCount = UsersModel::where(['com_id'=> $this->user['com_id']])->count();
			if($userCount >= $allowMaxNumber){
				$this->error('您的当前版本无法添加更多用户，若需要，请联系管理员开通更高版本');
			}else{
				$this->success('success');
			}
		}else{
			$this->error('请求方式错误');
		}
	}
}