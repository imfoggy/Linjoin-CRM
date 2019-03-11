<?php
namespace app\common\model;
use app\common\model\Users;
use think\model\concern\SoftDelete;
use think\Model;

class Clue extends Model{

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
	 * 获取器，自动完成性别转换。
	 * @Author Foggy
	 * @Date   2018-10-11
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $value [description]
	 * @return [type]                   [description]
	 */
	public function getSexAttr($value){
		$sex = ['m'=> '先生', 'w'=> '女士'];
		return $sex[$value];
	}

	/**
	 * 获取线索列表
	 * @Author Foggy
	 * @Date   2018-10-11
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  integer           $userid [description]
	 * @param  integer           $limit  [description]
	 * @param  integer           $public  [是否是线索池数据]
	 * @return [type]                    [description]
	 */
	public static function getClues($where = [], $limit = 10){
		$result = self::with('getCreateUser,getUser,getCountry')->where($where)->order('id desc')->paginate($limit)->each(function($item, $key){
			$item['diffDays'] = '-';
			if($item['clue_log_number'] <= 0 && $item['is_public'] == 0){
				//计算还有几天就会移动到线索池
				$endDate = date('Y-m-d', strtotime('+60 days',strtotime($item['update_time'])));
				$date = date('Y-m-d', time());
				$diffDays = diffBetweenTwoDays($endDate, $date);
				$item['diffDays'] = $diffDays;
			}
		});

		return $result;
	}

	/**
	 * 根据id获取到线索详情
	 * @Author Foggy
	 * @Date   2018-10-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  integer           $id [description]
	 * @return [type]                [description]
	 */
	public function getClueInfoById($id = 0){
		$result = self::get($id);
		$result->sexValue = $result->getData('sex');
		$result->clueLogs;
		foreach($result['clueLogs'] as $key=>&$value){
			$value['username'] = Users::getUsernameById($value['userid']);
		}
		$result->getCreateUser;
		$result->getUser;
		$result->userRecorder;
		$result->files;
		$result->getCountry;
		if($result['userRecorder']){
			foreach ($result['userRecorder'] as $key => &$value) {
				$value['old_username'] = Users::getUsernameById($value['old_user_id']);
				$value['new_username'] = Users::getUsernameById($value['new_user_id']);
			}
		}
		if($result['files']){
			foreach ($result['files'] as $key => &$value) {
				$value['username'] = Users::getUsernameById($value['userid']);
			}
		}
		return $result;
	}

	/**
	 * 更改负责人
	 * @Author Foggy
	 * @Date   2018-11-09
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  [type]            $id     [线索id]
	 * @param  [type]            $userid [新负责人id]
	 */
	public static function setUserid($id, $userid){
		if(!$id || !$userid){
			die('信息不存在或是用户信息不正确');
		}

		return self::where(['id'=> $id])->update(['userid'=> $userid]);
	}

	/**
	 * 关联创建者信息
	 * @Author Foggy
	 * @Date   2018-10-11
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getCreateUser(){
		return $this->belongsTo('Users','create_userid');
	}

	/**
	 * 关联负责人信息
	 * @Author Foggy
	 * @Date   2018-10-11
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function getUser(){
		return $this->belongsTo('Users','userid');
	}

	/**
	 * 关联沟通日志
	 * @Author Foggy
	 * @Date   2018-10-12
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function clueLogs(){
		return $this->hasMany('ClueLog', 'clue_id');
	}

	/**
	 * 查询范围【查询线索池里面的数据】
	 * @Author Foggy
	 * @Date   2018-10-16
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function scopePublic($query, $public = 0){
		$query->where(['is_public'=> $public]);
	}

	/**
	 * 负责人变更日志
	 * @Author Foggy
	 * @Date   2018-12-04
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function userRecorder(){
		return $this->hasMany('ClueUserRecorder', 'clue_id');
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
		return $this->hasMany('Files', 'out_id')->where('from', '=', 'Clue');
	}

	public function getCountry(){
		return $this->belongsTo('Countries', 'country_id')->field('id,name');
	}
}