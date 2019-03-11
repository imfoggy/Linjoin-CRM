<?php
namespace app\traits\controller;
use app\common\model\Log;
use app\common\model\Department;

trait Mytraits{
	
	/**
	 * 写日志
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function writeLog($userid = 0, $content = ''){
		$module = request()->module();
		$controller = request()->controller();
		$action = request()->action();
		$data['module'] = $module;
		$data['controller'] = $controller;
		$data['action'] = $action;
		$data['userid'] = $userid;
		$data['content'] = $content;
		if($log = Log::create($data)){
			return $log->id;
		}else{
			return false;
		}
	}

	/**
	 * 部门分类树
	 * @Author Foggy
	 * @Date   2018-10-17
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function departmentTrees(){
		
	}
}