<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Users as UsersModel;
use app\facade\Wechat;
use app\common\model\UsersEmailConfig;
use app\common\model\Company as CompanyModel;

class User extends Base{

	public function __construct(){
		parent::__construct();
		$sex = ['m'=> '男', 'w'=> '女'];
		$this->assign('_sexText', $sex);
		$this->user = session('loginUser');
		$this->assign('_userinfo', $this->user);
	}

	/**
	 * 用户编辑资料
	 * @Author Foggy
	 * @Date   2018-11-27
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function edit(Request $request, UsersModel $UsersModel){
		$info = $UsersModel->with('emailConfig')->get($this->user['id']);
		$this->assign('info', $info);
		return view();
	}

	/**
	 * 修改用户信息
	 * @Author Foggy
	 * @Date   2018-11-27
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request    [description]
	 * @param  UsersModel        $UsersModel [description]
	 * @return [type]                        [description]
	 */
	public function update(Request $request, UsersModel $UsersModel){
		$data = input('post.');
		$res = $UsersModel->where(['id'=> $this->user['id']])->update($data);
		if($res > 0){
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 绑定微信
	 * @Author Foggy
	 * @Date   2018-11-27
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function wechatQrcode(Request $request){
		$uid = $request->uid;
		if(!$uid){
			$this->error('用户信息不存在，请重新登录');
		}
		$userinfo = UsersModel::useGlobalScope(false)->get($uid);
		$res = Wechat::getUserInfo();
		$userinfo->openid = $res['id'];
		$userinfo->wx_name = $res['name'];
		$userinfo->wx_headimgurl = $res['avatar'];
		if(!$userinfo['avatar']){
			$userinfo->avatar = $res['avatar'];
		}
		$userinfo->save();
		$this->assign('userinfo', $userinfo);
		return view();
	}

	/**
	 * 修改登录密码
	 * @Author Foggy
	 * @Date   2018-11-27
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function modifypassword(Request $request){
		if(request()->isPost()){
			$oldPassword = input('post.old_password', '');
			$newPassword = input('post.new_password', '');
			$confirmPassword = input('post.confirm_password', '');
			if(!$oldPassword || !$newPassword || !$confirmPassword){
				$this->error('请正确填写各项内容');
			}
			if($newPassword !== $confirmPassword){
				$this->error('两次输入的密码不一致');
			}

			$userinfo = UsersModel::get(['id'=> $this->user['id']]);
			if($userinfo['password'] != act_password($oldPassword,'abcdef')){
				$this->error('原始密码输入错误');
			}
			//因为模型中已经定义了修改器，所以下面的密码不用加密处理。
			$userinfo->password = $newPassword;
			if($userinfo->save()){
				$this->success('密码修改成功');
			}else{
				$this->error('密码修改失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 用户详细信息
	 * @Author Foggy
	 * @Date   2018-11-28
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function read(Request $request){
		$uid = $request->uid;
		$userinfo = UsersModel::get($uid);
		return $userinfo;
	}

	/**
	 * 头像设置
	 * @Author Foggy
	 * @Date   2018-11-27
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function avatar(Request $request, UsersModel $UsersModel){
		$avatar = $request->avatar;
		if(!$avatar){
			$this->error('请上传头像文件');
		}
		$res = UsersModel::where(['id'=> $this->user['id']])->update(['avatar'=> $avatar]);
		if($res > 0){
			session('loginUser.avatar', $avatar);
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 绑定邮箱
	 * @Author Foggy
	 * @Date   2018-12-11
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function bindemail(UsersEmailConfig $UsersEmailConfig){
		if(request()->isPost()){
			$data = input('post.');
			$isHave = $UsersEmailConfig::where(['userid'=> $this->user['id']])->count();
			if($isHave > 0){
				$result = $UsersEmailConfig->save($data,['userid'=>$this->user['id']]);
			}else{
				$data['userid'] = $this->user['id'];
				$result = UsersEmailConfig::create($data);
			}

			if($result > 0){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}
}