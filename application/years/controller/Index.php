<?php
namespace app\years\controller;
use think\Controller;
use think\Request;
use app\facade\Wechat;
use app\common\model\Years2018 as Years2018Model;

class Index extends Controller{
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
			$post = array_filter(input('post.'));
			$isHas = Years2018Model::get(['openid'=> $post['openid']]);
			if($isHas['id'] > 0){
				$this->error('你的微信已经绑定过【'.$isHas['truename'].'】这个用户了', url('show',['id'=> $isHas['id']]));
			}
			$info = Years2018Model::create($post);
			if($info->id > 0){
				$this->success('操作成功', url('show',['id'=> $info->id]), $post);
			}else{
				$this->error('操作失败');
			}
		}else{
			$userinfo = WeChat::getUserInfo();
			if(!$userinfo['id']){
				die('获取信息失败，请重新刷新后尝试');
			}
			$this->assign('userinfo', $userinfo);
			return view();
		}
	}

	/**
	 * 展示绑定的信息
	 * @Author Foggy
	 * @Date   2019-01-04
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function show(Request $request){
		$id = $request->id;
		if(!$id){
			$this->error('id参数错误');
		}

		$userinfo = Years2018Model::get($id);
		if($userinfo['id'] <= 0 || !$userinfo){
			$this->error('信息不存在');
		}

		$this->assign('userinfo', $userinfo);
		return view();
	}
}