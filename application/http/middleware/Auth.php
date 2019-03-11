<?php
namespace app\http\middleware;
class Auth{
	/**
	 * 中间件，验证用户是否已经登录
	 * @Author Foggy
	 * @Date   2018-10-08
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $request [description]
	 * @param  \Closure          $next    [description]
	 * @return [type]                     [description]
	 */
	public function handle($request, \Closure $next){
		//验证用户是否已经登录
		$loginUser = session('loginUser');
		if(!$loginUser){
			return redirect('login/index');
		}
		return $next($request);
	}
}