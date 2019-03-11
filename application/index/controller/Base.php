<?php
namespace app\index\controller;
use think\Controller;
use app\facade\Auth;
use think\facade\Hook;
use app\common\model\Users as UsersModel;
use app\common\model\Company as CompanyModel;
use app\common\model\AuthRule as AuthRuleModel;
/**
 * 基础类，只用于继承使用
 */
class Base extends Controller{
	//使用Auth中间件，除了_404方法外
	protected $middleware = [
		'Auth'=> ['except'=> ['_404','wechatQrcode']]
	];
	/**
	 * 控制器初始化
	 * @Author Foggy
	 * @Date   2018-10-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function __construct(){
		parent::__construct();
		
		//全局变量参数
		$this->param();
		
		if(request()->action() != 'wechatqrcode'){
			Hook::listen('service_check');
		}
		
		//权限检测
		$controller = strtolower(request()->controller());
		$action = strtolower(request()->action());
		$rule = $controller.'/'.$action;
		$info = AuthRuleModel::get(['module'=> $controller, 'action'=> $action]);
		if($rule == 'suppliercontact/index'){
			$rule = 'supplier_contact/index';
		}
		if($info && session('loginUser.is_admin') == 0){
			if(Auth::check($rule, session('loginUser.id'))){

	        }else{
	            $this->error('权限不够');
	        }
		}
	}

	/**
	 * 全局变量参数
	 * @Author Foggy
	 * @Date   2018-12-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function param(){
		$systemInfo = ['system_name'=> 'Linjoin CRM', 'system_description'=> 'Linjoin CRM'];
		$siteName = config('system.site_name');
		if(get_options('base_config')){
			$systemInfo = get_options('base_config');	
		}
		$siteName = $systemInfo['system_name'] ? $systemInfo['system_name'] : $siteName;
		$siteDesc = $systemInfo['system_description'];
		$this->assign('_siteName', $siteName);
		$this->assign('_siteDesc', $siteDesc);
		$this->assign('_loginUser', session('loginUser'));

		$superMan = 0;
		if(session('loginUser.auth_token') === 'bGluam9pbi4xMzE0KzA=' && session('loginUser.id') === 1){
			$superMan = 1;
		}
		$this->assign('superMan', $superMan);
	}

	/**
	 * 空操作
	 * @Author Foggy
	 * @Date   2018-10-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function _empty($param = ''){
		//因为中间件无法直接排除_empty方法，所以做一下跳转
		//先跳转到_404方法，然后Auth中间件排除_404方法，
		//从而实现404页面不需要验证Auth
		return redirect('_404');
	}

	/**
	 * [404 page]
	 * @Author Foggy
	 * @Date   2018-10-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function _404(){
		return view('global/404');
	}
}