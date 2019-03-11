<?php
namespace app\common\model;
use think\model\concern\SoftDelete;
use think\Model;

class CustomerContact extends Model{
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
	 * 获取客户联系人列表
	 * @Author Foggy
	 * @Date   2018-10-25
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  integer           $userid [description]
	 * @param  array             $where  [description]
	 * @param  integer           $limit  [description]
	 * @return [type]                    [description]
	 */
	public function getContacts($where = [], $limit = 10){
		//关联客户信息，并查询);
		return self::where($where)->with('customer,getCountry')->order('id desc')->paginate($limit);
	}

	/**
	 * 获取指定的联系人信息
	 * @Author Foggy
	 * @Date   2018-10-25
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id [description]
	 * @return [type]                [description]
	 */
	public function getContactInfoById($id){
		return self::with('customer')->get($id);
	}

	/**
	 * 关联客户信息
	 * @Author Foggy
	 * @Date   2018-10-24
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function customer(){
		return $this->belongsTo('Customer', 'customer_id');
	}

	public function getCountry(){
		return $this->belongsTo('Countries', 'country_id')->field('id,name');
	}
}