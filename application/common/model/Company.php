<?php
namespace app\common\model;
use think\Model;
use think\model\concern\SoftDelete;
use app\common\model\Options as OptionsModel;
use app\common\model\ProductCategory as ProductCategoryModel;

class Company extends Model{
	protected $pk = 'id';

	// 开启自动写入时间戳字段
	protected $autoWriteTimestamp = 'datetime';
	//软删除字段
	protected $deleteTime = 'delete_time';

	/**
	 * 模型事件，数据新增后执行的事件。
	 * @Author Foggy
	 * @Date   2018-12-20
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	protected static function init()
    {
        self::afterInsert(function ($company, OptionsModel $OptionsModel, ProductCategoryModel $ProductCategoryModel) {
        	#初始化options表信息
            $options = config('options.');
            $comId = $company['id'];
            if($options['clue_from']){
            	$i = 0;
            	foreach($options as $key=> $value){
            		$d[$i]['com_id'] = $comId;
            		$d[$i]['key'] = $key;
            		$d[$i]['value'] = $value['value'];
            		$d[$i]['format'] = $value['format'];
            		$i++;
            	}

            	$OptionsModel->saveAll($d);
            }
            #初始化product_category,默认增加一个默认分类
            $ProductCategoryModel->save(['com_id'=> $comId, 'catname'=> '默认', 'desc'=> '默认分类']);
        });
    }

	public function getCompanys($where = [], $limit = 15){
		$result = self::where($where)->order('id desc')->paginate($limit)->each(function($item, $key){
			$date = date('Y-m-d', time());
			$item['diffDays'] = diffBetweenTwoDays($item['end_time'], $date);
		});
		return $result;
	}

	/**
	 * 关联管理员信息
	 * @Author Foggy
	 * @Date   2018-12-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function admin(){
		return $this->hasOne('Users', 'com_id')->where('is_admin', 1);
	}

	/**
	 * 关联所有用户
	 * @Author Foggy
	 * @Date   2018-12-19
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function users(){
		return $this->hasMany('Users', 'com_id');
	}
}