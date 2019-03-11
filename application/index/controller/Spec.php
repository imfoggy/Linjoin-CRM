<?php
namespace app\index\controller;
use think\Request;
use app\common\model\ProductSpec as SpecModel;
use app\common\model\ProductSpecValue as SpecValueModel;
use app\common\model\ProductCategory as ProductCategoryModel;

class Spec extends Base{

	use \app\traits\controller\Mytraits;

	/**
	 * 构造函数
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * 规格列表
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  integer           $customerId [description]
	 * @return [type]                        [description]
	 */
	public function index(){
		$list = [];
		$lists = SpecModel::getSpecs();
		$category = ProductCategoryModel::select()->toArray();
		$array = category_trees($category);
		$categoryHtml = category_html($array);
		$this->assign('categoryHtml', $categoryHtml);
		$this->assign('lists', $lists);
		return view();
	}

	/**
	 * 保存规格信息
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request   [description]
	 * @param  SpecModel         $SpecModel [description]
	 * @return [type]                       [description]
	 */
	public function save(Request $request, SpecModel $SpecModel)
    {
        $data = input('post.');
        $result = $this->validate($data, 'app\index\validate\Spec.create');
        if(true !== $result){
            $this->error($result);
        }
        $data['value'] = implode(',', $data['value']);
        if($SpecModel->allowField(true)->save($data)){
        	$data['value'] = explode(',', $data['value']);
        	foreach($data['value'] as $k=>$v){
        		$values[$k]['value'] = $v;
        	}
        	$spec = $SpecModel->values()->saveAll($values);
            $this->success('操作成功', url('index'));
        }else{
            $this->error('操作失败');
        }
    }

	/**
	 * 编辑规格页面
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function edit($id){
		if(!$id){
			$this->error('信息不存在');
		}
		$info = SpecModel::getSpecInfoById($id);
		if($info){
			$this->success('获取成功',$_SERVER["HTTP_REFERER"], $info);
		}else{
			$this->error('获取失败');
		}
	}

	/**
	 * 更新规格信息
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function update($id, SpecValueModel $SpecValueModel){
		if(!$id){
            $this->error('信息不存在');
        }
        $post = array_filter(input('post.'));
        $post['value'] = implode(',', $post['value']);
        $result = SpecModel::update($post,['id'=> $id]);
        $SpecValueModel->where(['spec_id'=> $id])->delete();
        $list = explode(',', $post['value']);
        foreach ($list as $key => $value) {
        	$d[$key]['spec_id'] = $id;
        	$d[$key]['value'] = $value;
        }
        $SpecValueModel->saveAll($d);
        if($result > 0){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
	}

	/**
	 * 删除规格信息
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function delete($id){
		if(!$id){
            $this->error('操作失败，请至少选择一条信息');
        }

        $str = $id;
        $idArray = $id;
        //判断id是单个还是多个
        if(is_array($id)){
            $str = implode(',', $id);
            $idArray = $id;
        }

        if(SpecModel::destroy($str)){
        	SpecValueModel::destroy(['spec_id'=> $id]);
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
	}

	/**
	 * 规格详情
	 * @Author Foggy
	 * @Date   2018-10-29
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function read($id){
		if(!$id){
			$this->error('信息不存在');
		}
		$info = SpecModel::getSpecInfoById($id);
		$this->assign('info', $info);
		return view();
	}
}