<?php
namespace app\index\controller;
use think\Request;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\ProductSpec as ProductSpecModel;
use app\common\model\ProductSpecValue as ProductSpecValueModel;

class Sku extends Base{

	use \app\traits\controller\Mytraits;

	public $config;

	public function __construct(){
		parent::__construct();
		$this->config = [
			'status'=> [
				1=> ['name'=> '已上架', 'color'=> 'green'], 
				2=> ['name'=> '已下架', 'color'=> 'red'],
			]
		];
		$this->user = session('loginUser');
		$this->assign('_config', $this->config);
	}

	/**
	 * sku的上下架管理
	 * @Author Foggy
	 * @Date   2018-11-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function status(Request $request){
		$id = $request->id;
		$status = $request->status;
		$res = ProductSkuModel::where(['id'=> $id])->update(['status'=> $status]);
		if($res > 0){
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 根据id来获取到sku信息
	 * @Author Foggy
	 * @Date   2018-11-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function skulist(Request $request){
		dump(input('post.'));
	}

	/**
	 * 删除一条SKU信息
	 * @Author Foggy
	 * @Date   2018-11-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id      [description]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function delete($id, Request $request){
		$id = $request->id;
		$result = ProductSkuModel::destroy($id);
		if($result > 0){
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 更新操作
	 * @Author Foggy
	 * @Date   2018-11-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id      [description]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function update($id, Request $request){
		$data = input('post.');
		$id = $request->id;
		$result = ProductSkuModel::update($data, ['id'=> $id]);
		if($result > 0){
			$this->success('操作成功');
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 获取到sku详细信息
	 * @Author Foggy
	 * @Date   2018-11-21
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function read($id, Request $request){
		$info = ProductSkuModel::get($id);
		$array = explode(':', $info['attributes']);
		$attrStr = '';
		foreach ($array as $k => &$v) {
			$valueInfo = ProductSpecValueModel::get($v);
			$specInfo = ProductSpecModel::get(['id'=> $valueInfo['spec_id']]);
			$attrStr .= $specInfo['name'].'：'.$valueInfo['value'].'|';
		}
		$info['attrStr'] = trim($attrStr, '|');

		if($info['id'] > 0){
			$this->success('数据获取成功',$_SERVER["HTTP_REFERER"], $info);
		}else{
			$this->error('数据获取失败');
		}
	}
}