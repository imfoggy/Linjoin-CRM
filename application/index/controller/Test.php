<?php
namespace app\index\controller;
use app\facade\Index;
use think\facade\Hook;

class Test extends Base{
	/**
	 * 测试门面1
	 * @Author Foggy
	 * @Date   2018-10-07
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]
	 */
	public function hehe(){
		echo Index::testStatic();
	}

	/**
	 * 测试门面2
	 * @Author Foggy
	 * @Date   2018-10-07
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]
	 */
	public function hehe2(){
		echo Index::testStatic2();
	}

	/**
	 * 测试钩子和行为
	 * @Author Foggy
	 * @Date   2018-10-07
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]
	 */
	public function testbehavior(){
		Hook::listen('mytest');
		echo '<br/>successfully';
	}

	public function mysql(){
		echo "string";
	}

	public function jacklove(){
		echo "shengxiangxilie";
	}
}