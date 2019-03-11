<?php
namespace app\index\controller;
use think\Request;
use mail\PHPMailer;
use mail\Myimap;
use app\common\model\EmailLog as EmailLogModel;
use app\common\model\UsersEmailConfig as UsersEmailConfigModel;

class Email extends Base{

	use \app\traits\controller\Mail;
	use \app\traits\controller\Mytraits;

	public $emailConfig;

	public function __construct(){
		parent::__construct();
		$this->user = session('loginUser');
		$this->emailConfig = UsersEmailConfigModel::get(['userid'=> $this->user['id']]);
		if(!$this->emailConfig['host'] || !$this->emailConfig['username'] || !$this->emailConfig['password'] || !$this->emailConfig['port']){
			$this->error('请正确配置邮箱信息后再来使用此功能');
		}

		$this->imap = new Myimap();
		$this->imap->username = $this->emailConfig['username'];
		$this->imap->userpwd = $this->emailConfig['password'];
		$this->imap->hostname = $this->emailConfig['host'];
		$this->imap->port = $this->emailConfig['port'];
	}

	/**
	 * 发送邮件
	 * @Author Foggy
	 * @Date   2018-11-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function send(Request $request, EmailLogModel $EmailLogModel){
		if(request()->isPost()){
			$email = input('post.users_values');
			$subject = input('post.subject');
			$content = input('post.content','',null);
			$attachment = input('post.file');
			$res = 0;
			if(count($email) > 0){
				foreach($email as $key=> $value){
					$res = self::sendEmail($value, $subject, $content, $attachment);
					if($res > 0){
						$d[$key]['email'] = $value;
						$d[$key]['userid'] = $this->user['id'];
						$d[$key]['subject'] = $subject;
						$d[$key]['content'] = $content;
						$d[$key]['file'] = input('post.file');
						$d[$key]['ext'] = input('post.ext');
						$d[$key]['size'] = input('post.size');
						$d[$key]['orign_name'] = input('post.orign_name');
						self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'给邮箱为'.$value.'的客户发送了一封邮件');
					}
				}

				$EmailLogModel->saveAll($d);
			}
			if($res > 0){
				$this->success('操作成功');
			}else{
				$this->error('操作失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 收件箱
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function mailbox(){
		$this->imap->open();
		$total = $this->imap->mailTotalCount();
		$this->assign('total', $total);
		return view();
	}

	/**
	 * 收取邮件
	 * @Author Foggy
	 * @Date   2018-12-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function receive(){
		//每页只显示一条数据
		$perPage = 15;
		//获取到当前页
		$currentPage = input('post.page');
		$this->imap->open();
		$res = $this->imap->listMessages($currentPage, $perPage, ['date','desc']);
		return $res['res'];
	}

	/**
	 * 获取未读邮件。
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getUnseen(){

	}

	/**
	 * 写信
	 * @Author Foggy
	 * @Date   2018-12-14
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function write(Request $request){
		$msgid = $request->msgid;
		if($msgid > 0){
			$this->imap->open();
			$header = $this->imap->getHeader($msgid);
			$this->assign('header', $header);
		}
		return view();
	}

	/**
	 * 标记为已读
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function setseen(){
		if(request()->isPost()){
			$data = input('post.msgids');
			$this->imap->open();
			if(count($data) > 0){
				foreach($data as $value){
					$res = $this->imap->mailRead($value);
				}

				if($res > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error('没有选中邮件');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 删除邮件
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function delete(){
		if(request()->isPost()){
			$data = input('post.msgids');
			$this->imap->open();
			if(count($data) > 0){
				foreach($data as $value){
					$res = $this->imap->delete($value);
				}

				if($res > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error('没有选中邮件');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 草稿箱
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function draft(){
		if(request()->isPost()){
			$data = input('post.msgids');
			$this->imap->open();
			if(count($data) > 0){
				foreach($data as $value){
					$res = $this->imap->draftMail($value);
				}

				if($res > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error('没有选中邮件');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 标记为未读
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function setflagged(){
		if(request()->isPost()){
			$data = input('post.msgids');
			$this->imap->open();
			if(count($data) > 0){
				foreach($data as $value){
					$res = $this->imap->mailFlagged($value);
				}

				if($res > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error('没有选中邮件');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 标记为未读
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function cancelflagged(){
		if(request()->isPost()){
			$data = input('post.msgids');
			$this->imap->open();
			if(count($data) > 0){
				foreach($data as $value){
					$res = $this->imap->mailCancelFlagged($value);
				}

				if($res > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error('没有选中邮件');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 邮件详情
	 * @Author Foggy
	 * @Date   2018-12-13
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function read(Request $request){
		$info = [];
		$this->imap->open();
		$info['header'] = $this->imap->getHeader($request->msgid);
		$info['header']['attachNumber'] = count($info['header']['attach']);
		$info['content'] = $this->imap->getOne($request->msgid);
		$this->assign('info', $info);
		return view();
	}

	/**
	 * 下载附件
	 * @Author Foggy
	 * @Date   2018-12-14
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function downloadattach(Request $request){
		$msgid = $request->msgid;
		$path = './attach/';
		$this->imap->open();
		$file = $this->imap->downloadAttachment($msgid, $path);
		if(count($file) > 0){
			$download =  new \think\response\Download(getcwd().'/attach/'.$file[0]);
        	return $download->name($file[0]);
		}else{
			$this->error('下载失败');
		}
	}
}