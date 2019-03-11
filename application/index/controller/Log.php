<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Log as LogModel;
use app\common\model\Users as UsersModel;

class Log extends Controller{

	public function __construct(){
		parent::__construct();
	}

	/**
	 * 阅读日志
	 * @Author Foggy
	 * @Date   2018-11-28
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function read(Request $request){
		$uid = $request->uid;
		$date = $request->date;
		if(!$uid){
			$this->error('用户id参数错误');
		}
		$userinfo = UsersModel::get($uid);
		$this->assign('userinfo', $userinfo);
		$where[] = ['userid','=', $uid];
		$where[] = ['date', '=',$date];
		$lists = LogModel::where($where)->select();
		$this->assign('lists', $lists);
		return view();
	}
}