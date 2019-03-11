<?php
namespace app\traits\controller;
use mail\PHPMailer;
use mail\Myimap;
use app\common\model\UsersEmailConfig as UsersEmailConfigModel;

trait Mail{

	public $config = [];

	/**
	 * 发送邮件
	 * @Author Foggy
	 * @Date   2018-10-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function sendEmail($email, $subject, $content, $attachment){
		$this->user = session('loginUser');
		$this->emailConfig = UsersEmailConfigModel::get(['userid'=> $this->user['id']]);
		if(!$this->emailConfig['send_host'] || !$this->emailConfig['username'] || !$this->emailConfig['password'] || !$this->emailConfig['send_port']){
			$this->error('请正确配置邮箱信息后再来使用此功能');
		}
		if(!$this->emailConfig){
			return false;
		}
		$toemail = $email;//定义收件人的邮箱

		$mail = new PHPMailer;

		$mail->isSMTP();// 使用SMTP服务
		$mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
		$mail->Host = $this->emailConfig['send_host'];// 发送方的SMTP服务器地址
		$mail->SMTPAuth = true;// 是否使用身份验证
		$mail->Username = $this->emailConfig['username'];// 发送方的163邮箱用户名，就是你申请163的SMTP服务使用的163邮箱
		$mail->Password = $this->emailConfig['password'];// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！
		$mail->SMTPSecure = "ssl";// 使用ssl协议方式
		$mail->Port = $this->emailConfig['send_port'];// 163邮箱的ssl协议方式端口号是465/994
		$mail->isHTML(true);  //设置支持html
		$mail->setFrom($this->emailConfig['username'], $this->emailConfig['username']);// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示
		$mail->addAddress($toemail);// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
		$mail->addReplyTo($this->emailConfig['username'],"Reply");// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
		//$mail->addCC("xxx@163.com");// 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
		//$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)
		if($attachment){
			$mail->addAttachment(getcwd().'/uploads/'.$attachment);// 添加附件
		}

		$mail->Subject = $subject;// 邮件标题
		$mail->Body = $content;// 邮件正文
		//$mail->AltBody = "This is the plain text纯文本";// 这个是设置纯文本方式显示的正文内容，如果不支持Html方式，就会用到这个，基本无用

		// if(!$mail->send()){// 发送邮件
		//     echo "Message could not be sent.";
		//     echo "Mailer Error: ".$mail->ErrorInfo;// 输出错误信息
		// }else{
		//     echo '发送成功';
		// }
		return $mail->send();
	}

	/**
	 * 收取邮件
	 * @Author Foggy
	 * @Date   2018-12-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function receiveEmail(){
		$o = new Myimap();
		$o->username="";
		$o->userpwd="";
		$o->hostname="";
		$o->open();
		//从第二条取到第六条
		//dump($o->mailList("2:6"));
		return $o->mailList();
		//echo($o->getOne(1));
	    //$o->mailDelete(1);
	    //$o->getAttach(7,'./');
	    //$o->getAttachData('./','weixin.php');
	}

	/**
	 * 邮件详细信息
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $msgid [description]
	 * @return [type]                   [description]
	 */
	public function read($msgid){
		
	}
}