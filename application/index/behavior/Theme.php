<?php
namespace app\index\behavior;
use think\facade\View;
/**
 * 根据不同的设备，切换不同的主题
 */
class Theme{
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
		$view_path = env('app_path').request()->module().'/view/';
		if(request()->isMobile() !== true){
			$view_path .= 'default/';
		}else{
			$view_path .= 'default/';
		}

		View::config('view_path', $view_path);
	}
}