<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Files as FilesModel;

class Files extends Base{

	public $user;

	public function __construct(){
		parent::__construct();
		$this->user = session('loginUser');
	}

	/**
	 * 保存文件
	 * @Author Foggy
	 * @Date   2018-12-05
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function save(Request $request){
		if(request()->isPost()){
			$data = array_filter(input('post.'));
			$data['userid'] = $this->user['id'];
			$data['create_time'] = date('Y-m-d H:i:s', time());
			$result = FilesModel::create($data);
			if($result > 0){
				$this->success('上传成功',$_SERVER['HTTP_REFERER'], $result);
			}else{
				$this->error('上传失败');
			}
		}else{
			$this->error('请求方式错误');
		}
	}

	/**
	 * 删除文件
	 * @Author Foggy
	 * @Date   2018-12-05
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function delete(Request $request){
		$id = $request->id;
		$res = FilesModel::where(['id'=> $id])->delete();
		if($res > 0){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

	/**
	 * 文件下载
	 * @Author Foggy
	 * @Date   2018-12-05
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function download(Request $request){
		$id = $request->id;
		$info = FilesModel::get($id);
		if(!$info){
			$this->error('文件不存在');
		}
		return download(getcwd().'/uploads/'.$info['file'], $info['orign_name']);
	}
}