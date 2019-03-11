<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use app\common\model\Product as ProductModel;
use app\common\model\ProductSku as ProductSkuModel;
use app\common\model\ProductSpecValue as ProductSpecValueModel;
use app\common\model\ProductSpec as ProductSpecModel;
use app\common\model\Users as UsersModel;
use think\Model;

class Contract extends Model{
	//启用软删除
	use SoftDelete;

	protected $pk = 'id';
	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';
	//新增的时候自动完成
	protected $insert = ['com_id'];
	// 定义全局的查询范围
    protected $globalScope = ['com'];
    // 设置json类型字段
	protected $json = ['payment_desc'];
    
    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

	/**
	 * 新增的时候自动增加com_id
	 * @Author Foggy
	 * @Date   2018-12-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 */
	protected function setComIdAttr($value){
		return session('loginUser.com_id');
	}

	/**
	 * 全局查询条件
	 * @Author Foggy
	 * @Date   2018-12-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $query [description]
	 * @return [type]                   [description]
	 */
    public function scopeCom($query)
    {
        $query->where('com_id',session('loginUser.com_id'));
    }

    /**
     * 获取器
     * @Author Foggy
     * @Date   2018-12-24
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $value [description]
     * @return [type]                   [description]
     */
    // public function getSignTimeAttr($value){
    // 	if($value === '0000-00-00'){
    // 		return '';
    // 	}
    // }

    // public function getEffectTimeAttr($value){
    // 	if($value === '0000-00-00'){
    // 		$value = '';
    // 	}
    // }

    // public function getShipmentTimeAttr($value){
    // 	if($value === '0000-00-00'){
    // 		$value = '';
    // 	}
    // }

    // public function getArrivalTimeAttr($value){
    // 	if($value === '0000-00-00'){
    // 		$value = '';
    // 	}
    // }

	/**
	 * 获取到合同列表
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getContracts($where = [], $limit = 10){
		$result = self::with('getCreateUser,getSignatoryUser,getApproversUser,getCustomerUser,getStartPortCountry,getEndPortCountry')->where($where)->order('id desc')->paginate($limit);
		return $result;
	}

	/**
	 * 获取到合同的详细信息
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function getContractInfo($id){
		$info = self::with('contractProducts,getCreateUser,getSignatoryUser,getApproversUser,getCustomerUser,getStartPortCountry,getEndPortCountry,files,getCountry')->get($id);
		if(count($info['contract_products']) > 0){
			foreach ($info['contract_products'] as $key => &$value) {
				#1.获取到产品名称
				$value['product_name'] = ProductModel::where(['id'=> $value['product_id']])->value('product_name');
				$unitId = ProductModel::where(['id'=> $value['product_id']])->value('unit_id');
				$value['unitText'] = get_options('product_unit')[$unitId];
				$attributes = ProductSkuModel::where(['id'=> $value['skuid']])->value('attributes');
				$attrStr = '-';
				if($attributes){
					$array = explode(':', $attributes);
					foreach ($array as $k => $v) {
						$valueInfo = ProductSpecValueModel::get($v);
						$specInfo = ProductSpecModel::get(['id'=> $valueInfo['spec_id']]);
						$attrStr .= $specInfo['name'].'：'.$valueInfo['value'].'|';
					}
					$attrStr = trim($attrStr, '|');
				}
				$sumPrice += $value['total'];
				$value['attrStr'] = $attrStr;
			}
		}
		if($info['files']){
			foreach ($info['files'] as $key => &$value) {
				$value['username'] = UsersModel::getUsernameById($value['userid']);
			}
		}
		$info['sum_price'] = $sumPrice > 0 ? $sumPrice : 0;

		return $info;
	}

	/**
	 * 关联
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function contractProducts(){
		return $this->hasMany('ContractProducts', 'contract_id');
	}

	/**
	 * 关联合同创建人信息
	 * @Author Foggy
	 * @Date   2018-11-22
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getCreateUser(){
		return $this->belongsTo('Users', 'create_userid');
	}

	/**
	 * 获取到签约人信息
	 * @Author Foggy
	 * @Date   2018-11-22
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getSignatoryUser(){
		return $this->belongsTo('Users', 'signatory_userid');
	}

	/**
	 * 获取到审批人信息
	 * @Author Foggy
	 * @Date   2018-11-22
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getApproversUser(){
		return $this->belongsTo('Users', 'approvers_userid');
	}

	/**
	 * 获取到关联客户信息
	 * @Author Foggy
	 * @Date   2018-11-22
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getCustomerUser(){
		return $this->belongsTo('Customer', 'from_customer_id');
	}

	/**
	 * 获取到起运港国家
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getStartPortCountry(){
		return $this->belongsTo('Countries', 'start_port_country')->field('id,name');
	}

	/**
	 * 获取到目的港国家
	 * @Author Foggy
	 * @Date   2018-11-26
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getEndPortCountry(){
		return $this->belongsTo('Countries', 'end_port_country')->field('id,name');
	}

	/**
	 * 关联上传的文件
	 * @Author Foggy
	 * @Date   2018-12-05
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function files(){
		return $this->hasMany('Files', 'out_id')->where('from', '=', 'Contract');
	}

	public function getCountry(){
		return $this->belongsTo('Countries', 'country_id')->field('id,name');
	}
}