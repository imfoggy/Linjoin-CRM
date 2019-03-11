<?php
namespace app\weixin\controller;
use EasyWeChat\Foundation\Application;
use think\Controller;

class Weixin extends Controller{

	public $config;
	public $app;

	/**
	 * 构造方法，初始化
	 * @Author Foggy
	 * @Date   2018-10-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function __construct(){
		$this->config = config('wechat.');
		$this->app = new Application($this->config);
	}
	/**
	 * 自动回复
	 * @Author Foggy
	 * @Date   2018-10-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function sever(){
		$server = $this->app->server;
		$server->setMessageHandler(function ($message) {
			$fromUserName = $message->FromUserName;
		    switch ($message->MsgType) {
		        case 'event':
		            if($message->Event == 'subscribe'){
		            	$userinfo = $this->userinfo($fromUserName);
		            	$sex = [1=> '先生', 2=> '女士'];
		            	return '亲爱的 '.$userinfo['nickname'].'，欢迎关注Linjoin CRM ';
		            }else{
		            	return '?????';
		            }
		            break;
		        case 'text':
		            return '亲爱的 '.$userinfo['nickname'].'，欢迎关注Linjoin CRM ';
		            break;
		        case 'image':
		            return '收到图片消息';
		            break;
		        case 'voice':
		            return '收到语音消息';
		            break;
		        case 'video':
		            return '收到视频消息';
		            break;
		        case 'location':
		            return '收到坐标消息';
		            break;
		        case 'link':
		            return '收到链接消息';
		            break;
		        // ... 其它消息
		        default:
		            return '收到其它消息';
		            break;
		    }
		});

		$server->serve()->send();
	}

	/**
	 * 网页授权
	 * @Author Foggy
	 * @Date   2018-10-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getUserInfo(){
		$this->config['oauth'] = [
	        'scopes'   => ['snsapi_userinfo'],
	        'callback' => 'weixin.php/weixin/callback',
	    ];
		if(!session('wechat_userinfo')){
			$oauth = $this->app->oauth;
			$callbackUrl = request()->url(true);
			session('wechat_target_url', $callbackUrl);
			//return $oauth->redirect();
			$oauth->redirect()->send();
		}

		$userinfo = session('wechat_userinfo');
		return $userinfo;
		//dump($userinfo);
		// echo '***************************************';
		// dump($this->userinfo($userinfo['id']));
	}

	/**
	 * 微信授权回调页面
	 * @Author Foggy
	 * @Date   2018-10-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return function          [description]
	 */
	public function callback(){
		$oauth = $this->app->oauth;
		// 获取 OAuth 授权结果用户信息
		$user = $oauth->user();
		session('wechat_userinfo', $user->toArray());
		$targetUrl = empty(session('wechat_target_url')) ? '/' : session('wechat_target_url');
		header('Location:'.$targetUrl);
	}

	/**
	 * 获取已关注用户信息(单个用户)
	 * @Author Foggy
	 * @Date   2018-10-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $openid [description]
	 * @return [type]                    [description]
	 */
	public function userinfo($openid){
		if(!$openid){
			$this->error('openid参数错误');
		}

		$userService = $this->app->user;
		$user = $userService->get($openid);
		return $user;
	}

	/**
	 * 根据openid批量获取用户信息(已关注用户)
	 * @Author Foggy
	 * @Date   2018-10-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  array             $lists [$openid1, $openid2, ...]
	 * @return [type]                   [description]
	 */
	public function batchUserinfo($lists = ['ofCo01teRLVmziU9AbXefR8PiR8I','ofCo01lxjaIQPquH6jCZwKgU2dUo']){
		if(!is_array($lists) || count($lists) <= 0){
			$this->error('用户列表参数错误');
		}
		$userService = $this->app->user;
		try {
			$users = $userService->batchGet($lists);
		} catch (\Exception $e) {
			trace($e->getMessage(),'error');
		}
		dump($users);
	}

	/**
	 * 获取已关注的所有用户
	 * @Author Foggy
	 * @Date   2018-10-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getAllUsers(){
		$lists = $this->getUsers();
		return $this->batchUserinfo($lists['data']['openid']);
	}

	/**
	 * 获取用户列表
	 * @Author Foggy
	 * @Date   2018-10-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getUsers(){
		$userService = $this->app->user;
		$users = [];
		// $nextOpenId 可选
		$users = $userService->lists($nextOpenId = null);
		//如果有数据
		if($users->total > 0){
			$users = $users->toArray();
		}
		/*
		array(4) {
		  ["total"] => int(2)
		  ["count"] => int(2)
		  ["data"] => array(1) {
		    ["openid"] => array(2) {
		      [0] => string(28) "ofCo01teRLVmziU9AbXefR8PiR8I"
		      [1] => string(28) "ofCo01lxjaIQPquH6jCZwKgU2dUo"
		    }
		  }
		  ["next_openid"] => string(28) "ofCo01lxjaIQPquH6jCZwKgU2dUo"
		}
		 */
		return $users;
	}

	/**
	 * 模板消息·
	 * @Author Foggy
	 * @Date   2018-10-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function templateMsg($toOpenid = 'ofCo01teRLVmziU9AbXefR8PiR8I', $data, $url = ''){
		$notice = $this->app->notice;
		$templateId = '6cOBFbp4Z8LvBtsJfjb8eAmUcelcyRJHp_FSt2ZWWQY';
		// $data = array(
		//          "first"  => "今中午吃屎吧~~",
		//          "keyword1"   => "桐哥",
		//          "keyword2"  => date('Y-m-d H:i:s'),
		//          "keyword3"  => "老邢建议今中午吃屎~~",
		//          "remark" => "你觉得咋样？",
		//         );
		try {
			return $notice->uses($templateId)->withUrl($url)->andData($data)->andReceiver($toOpenid)->send();
		} catch (\Exception $e) {
			trace($e->getMessage(),'error');
		}
	}

	/**
	 * 设置自定义菜单
	 * @Author Foggy
	 * @Date   2018-10-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function setMenu(){
		$menu = $this->app->menu;
		$buttons = [
		    [
		        "type" => "view",
		        "name" => "进入CRM",
		        //"url"  => request()->domain()."/index.php/index/login/wxlogin"
		        "url"  => "https://crm.linjoin.cn/index.php/index/login/wxlogin"
		    ],
		    // [
		    //     "name"       => "多个菜单",
		    //     "sub_button" => [
		    //         [
		    //             "type" => "view",
		    //             "name" => "测试菜单一",
		    //             "url"  => "http://foggy.mynatapp.cc/weixin.php/weixin/getuserinfo"
		    //         ],
		    //         [
		    //             "type" => "view",
		    //             "name" => "测试菜单二",
		    //             "url"  => "http://foggy.mynatapp.cc/weixin.php/weixin/getuserinfo"
		    //         ],
		    //         [
		    //             "type" => "view",
		    //             "name" => "测试菜单三",
		    //             "url" => "http://foggy.mynatapp.cc/weixin.php/weixin/getuserinfo"
		    //         ]
		    //     ]
		    // ],
		    // [
		    // 	"type"=> "view",
		    // 	"name"=> "类似菜单",
		    // 	"url"=> "http://foggy.mynatapp.cc/weixin.php/weixin/getuserinfo"
		    // ]
		];
		$result = $menu->add($buttons);
		dump($result);
	}

	/**
	 * 获取到设置的自定义菜单
	 * @Author Foggy
	 * @Date   2018-10-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getMenu(){
		$menu = $this->app->menu;
		$menus = $menu->all();
		dump($menus);
	}
}