<?php
namespace app\index\controller;
use app\common\model\Users as UserModel;
use think\Request;
use think\Controller;
use app\facade\Wechat;

class Login extends Controller{
	use \app\traits\controller\Mail;
	use \app\traits\controller\Mytraits;
	/**
	 * 登录首页
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function index(){
		if(request()->isPost()){
			$username = input('post.username');
			$password = input('post.password');
			$result = $this->validate(
				['username'  => $username, 'password' => $password],
				'app\index\validate\User.login'
			);
			if(true !== $result){
				return apiFail($result);
			}
			$userinfo = UserModel::getUserInfoByName($username);
			if(!$userinfo){
				return apiFail('账号不存在');
			}

			if($userinfo['status'] == 2){
				return apiFail('此账号已被停用');
			}
			
			$password = act_password($password, $userinfo['crypt']);
			if($userinfo['password'] != $password){
				return apiFail('密码错误');
			}

			if($userinfo['username'] == $username && $userinfo['password'] == $password){
				session('loginUser', $userinfo);
				self::writeLog($userinfo['id'], '用户'.$userinfo['username'].'在'.date('Y-m-d H:i:s',time()).'登录了CRM系统');
				return ['code'=> 200,'msg'=> '登录成功，即将进入CRM系统', 'nextUrl'=> url('index/index')];
			}else{
				return apiFail('登录失败');
			}
		}else{
			return view();
		}
	}

	/**
	 * 微信端
	 * @Author Foggy
	 * @Date   2018-12-01
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function wxlogin(Request $request){
		if(!session('wechat_userinfo')){
			Wechat::getUserInfo();
		}else{
			$wxinfo = session('wechat_userinfo');
			//检测是否有绑定此微信用户的微信
			$user = UserModel::get(['openid'=> $wxinfo['id']]);
			if($user['id'] > 0){
				//如果此微信用户已经绑定过了账号，就直接登录进去。
				session('loginUser', $user);
				$this->redirect(url('index/index'));
			}else{
				//若是没有绑定的话，就只能通过账号和密码来登录
				$this->redirect(url('login/index'));
			}
		}
	}

	/**
	 * 退出登录
	 * @Author Foggy
	 * @Date   2018-10-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function logout(){
		if(!session('loginUser')){
			$this->error('您当前未登录账号');
		}

		$userinfo = session('loginUser');
		session('loginUser', null);

		if(!session('?loginUser')){
			self::writeLog($userinfo['id'],'用户'.$userinfo['username'].'在'.date('Y-m-d H:i:s',time()).'退出了CRM系统');
			$this->success('退出登录中...', url('login/index'));
		}else{
			$this->error('退出失败，请稍后再试', url('login/index'));
		}
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
		return view('global/404');
	}

	/**
	 * 忘记密码的相关操作
	 * @Author Foggy
	 * @Date   2018-12-25
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function forget(Request $request){
		$action = $request->action ? $request->action : 'index';
		switch ($action) {
			case 'index':
				return view('forget_index');
				break;
			case 'sendemail':
				$email = trim($request->email);
				$options = config('system.email');

				if ($email) {
					$userinfo = UserModel::useGlobalScope(false)->get(['email'=> $email]);
					if($userinfo['id'] <= 0){
						$this->error('邮箱不存在');
					}

					$mail = new \mail\PHPMailer;
					$mail->isSMTP();// 使用SMTP服务
					$mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
					$mail->Host = $options['host'];// 发送方的SMTP服务器地址
					$mail->SMTPAuth = true;// 是否使用身份验证
					$mail->Username = $options['username'];// 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱
					$mail->Password = $options['password'];// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！
					$mail->SMTPSecure = "ssl";// 使用ssl协议方式
					$mail->Port = $options['port'];// 163邮箱的ssl协议方式端口号是465/994
					$mail->isHTML(true);  //设置支持html
					$mail->setFrom($options['username'], $options['username']);// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示
					$mail->addAddress($email);// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
					$mail->addReplyTo($options['username'],"Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
					$mail->Subject = 'Linjoin CRM 找回密码';// 邮件标题
					$hash = base64_encode($email.'+--'.date('YmdHis',time()).mt_rand(10000,99999));
					$domain = request()->domain().url('login/forget',['action'=>'active', 'token'=>$hash]);
					$content = <<<EOT
					您好，欢迎使用Linjon CRM ，您可以通过点击下面的链接地址来重置您的密码（一个小时内点击有效）：
					<br><a href="$domain" style="height:30px;color:#ffffff;background:#7266ba;line-height:30px;text-decoration:none;padding:5px;">点击这里来重置密码</a>
					<br>
					<br>
					<br>若点击上面的按钮无反应，请手动复制下面的链接地址到浏览器中打开。
					<br>$domain

EOT;
					$mail->Body = $content;// 邮件正文
					if(!$mail->send()){// 发送邮件
					    $this->error($mail->ErrorInfo);
					}else{
						$data = [
							'email'=> $email,
							'token'=> $hash,
							'expire_time'=> date('Y-m-d H:i:s', strtotime('+1 hours', time())),
							'create_time'=> date('Y-m-d H:i:s', time())
						];
						db('forget_token')->insert($data);
					    $this->success('发送成功，请通过邮件中的提示内容来重置您的密码');
					}
				}
				break;
			case 'active':
				if(request()->isPost()){
					$data = input('post.');
					if(!$data['token']){
						$this->error('非法访问');
					}
					if($data['password'] != $data['confirmpassword']){
						$this->error('两次输入的密码不一致');
					}
					$info = db('forget_token')->where(['token'=> $data['token']])->order('id desc')->find();
					if($info['id'] > 0){
						$time = strtotime($info['expire_time']);
						if($time < time()){
							$this->error('邮件已经过期，请重新发送');
						}
						$token = base64_decode($info['token']);
						$ex = explode('+--', $token);
						$userinfo = UserModel::useGlobalScope(false)->get(['email'=> $ex[0]]);
						
						$userinfo->password = $data['password'];
						$userinfo->update_time = date('Y-m-d H:i:s',time());
						if($userinfo->save()){
							db('forget_token')->where(['id'=> $info['id']])->update(['status'=> 1]);
							$this->success('密码重置成功',url('login/index'));
						}else{
							$this->error('密码重置失败');
						}
					}else{
						$this->error('访问错误');
					}
				}else{
					$token = $request->token;
					$this->assign('token', $token);
					$info = db('forget_token')->where(['token'=> $token])->order('id desc')->find();
					if($info['id'] <= 0){
						$this->error('访问错误');
					}
					if($info['status'] == 1){
						$this->error('链接已失效，请重新发送邮件');
					}
					return view('forget_active');
				}
				break;
			default:
				# code...
				break;
		}
	}
}