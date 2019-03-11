<?php
namespace app\index\behavior;
use think\Controller;
use app\common\model\Users as UsersModel;
use app\common\model\Company as CompanyModel;

/**
 * 判断用户账号和公司是否被禁用或是到期
 */

class ServiceCheck extends Controller{
	/**
	 * 钩子入口方法
	 * @Author Foggy
	 * @Date   2018-10-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $params [description]
	 * @return [type]                    [description]
	 */
	public function run($params){
		//检查用户是否被禁用或是用户所在的公司是否已经被禁用或是到期
		$uid = session('loginUser.id');
		$comId = session('loginUser.com_id');
		if(!$uid || !$comId){
			$this->redirect(url('login/index'));
		}
		$userinfo = UsersModel::get($uid);
		if(!$userinfo['id']){
			die('用户不存在');
		}
		if($userinfo['status'] == 2){
			die('您的账户已经被管理员禁用！');
		}
		$companyinfo = CompanyModel::get($userinfo['com_id']);
		if(!$companyinfo['id']){
			die('公司不存在');
		}

		if($companyinfo['is_ban'] == 1){
			die('公司账号已被禁用');
		}

		$today = strtotime(date('Y-m-d', time()));
		$endtime = strtotime($companyinfo['end_time']);
		if($endtime < $today){
			die('您的公司账号已过期，请联系管理员开通');
		}
	}
}