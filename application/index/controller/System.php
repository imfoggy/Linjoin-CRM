<?php
namespace app\index\controller;
use think\Controller;

class System extends Base{

	use \app\traits\controller\Mytraits;

	public $config;
	/**
	 * 构造函数，进行数据初始化
	 * @Author Foggy
	 * @Date   2018-10-17
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function __construct(){
		parent::__construct();
		$this->config = [
            'baseConfig'=> get_options('base_config'),
        ];

        $this->assign('_config', $this->config);
	}

	/**
	 * 系统设置首页面
	 * @Author Foggy
	 * @Date   2018-10-17
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(){
		if(request()->isPost()){
			$json = json_encode(input('post.'));
			$result = set_option('base_config', $json, 'json');
			if($result > 0){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}else{
			return view();
		}
	}

	/**
	 * 微信配置
	 * @Author Foggy
	 * @Date   2018-11-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function wechat(){
		return view();
	}

	/**
	 * 邮箱配置
	 * @Author Foggy
	 * @Date   2018-11-10
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function email(){
		if(request()->isPost()){
			$data = input('post.');
			if(empty($data)){
				$this->error('请正确填写各项参数');
			}

			$json = json_encode($data);
			$res = set_option('email_config', $json);
			if($res > 0){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}else{
			$info = get_options('email_config');
			$this->assign('info', $info);
			return view();
		}
	}
}