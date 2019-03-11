<?php
namespace app\api\controller;
use think\Controller;

class Upload extends Controller{
	/**
	 * 单文件上传
	 * @Author Foggy
	 * @Date   2018-10-16
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function uploadOne(){
		$file = request()->file('file');
		$orignInfo = $file->getInfo();
		//验证规则
		$ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'zip', 'rar', 'txt', 'doc', 'docx','ppt', 'pptx', 'xls', 'xlsx'];
		//size默认是按照字节来计算的
		$validate = ['size'=> 5 * 1024 * 1024, 'ext'=> $ext];
		// 移动到框架应用根目录/uploads/ 目录下
        $info = $file->validate($validate)->move( './uploads');
        if($info){
        	$ext = $info->getExtension();
        	$size = $orignInfo['size'];
        	$orignName = $orignInfo['name'];
        	return apiSuccess('上传成功',['file'=> $info->getSaveName(), 'ext'=> $ext, 'size'=> $size, 'orignName'=> $orignName]);
        }else{
        	return apiFail($file->getError());
        }
	}
}