<?php
namespace app\years\controller;
use think\Controller;
use think\Request;
use app\facade\Wechat;

class Years extends Controller{
	//设置开关
	private $switch = 1;

	public function __construct(){
		parent::__construct();
		if($this->switch !== 1){
			die('管理员关闭了此活动');
		}
	}

	/**
	 * 输出模板+获取到公司员工信息
	 * @Author Foggy
	 * @Date   2019-01-03
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(Request $request){
		if(request()->isPost()){
			echo "string";
		}else{
			$userinfo = WeChat::getUserInfo();
			if(!$userinfo['id']){
				die('获取信息失败，请重新刷新后尝试');
			}
			$this->assign('userinfo', $userinfo);
			return view();
		}
	}
}