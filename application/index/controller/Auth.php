<?php
namespace app\index\controller;
use think\Request;
use app\common\model\AuthGroup as AuthGroupModel;

class Auth extends Base{
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 获取到权限
	 * @Author Foggy
	 * @Date   2018-12-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function get(Request $request){
		if(request()->isPost()){
			$groupId = input('post.groupId');
			$info = AuthGroupModel::get($groupId);
			$array = [];
			if($info['rules']){
				$array = explode(',', $info['rules']);
			}

			return $array;
		}else{
			$this->error('获取权限失败');
		}
	}

	/**
	 * 权限设置
	 * @Author Foggy
	 * @Date   2018-12-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 */
	public function set(Request $request){
		if(request()->isPost()){
			$data = array_filter(input('post.'));
			$groupId = $data['auth_group_id'];
			$auths = $data['per'];
			if(count($auths) <= 0){
				$this->error('请至少选择一个权限');
			}

			foreach ($auths as $key => $value) {
				$authStr .= $key.',';
			}

			$authStr = trim($authStr, ',');

			$authInfo = AuthGroupModel::get($groupId);
			$authInfo->rules = $authStr;
			if($authInfo->save()){
				$this->success('授权成功');
			}else{
				$this->error('操作失败');
			}
		}else{

		}
	}
}