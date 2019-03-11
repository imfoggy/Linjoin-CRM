<?php
namespace app\crontab\controller;
use think\Controller;
use app\facade\Wechat;
use think\helper\Time;
use app\common\model\Clue as ClueModel;
use app\common\model\LogBoss as LogBossModel;
use app\common\model\Customer as CustomerModel;
use app\common\model\CrontabLog as CrontabLogModel;

/**
 * 计划任务类
 */
class Crontab extends Controller{

	public $config;

	public function __construct(){
		parent::__construct();
		$this->config = [
			'baseConfig'=> get_options('base_config')
		];
	}

	/**
	 * 线索移动到线索池
	 * @Author Foggy
	 * @Date   2018-11-22
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function clueBatchToPublic(ClueModel $ClueModel){
		debug('begin');
		if($this->config['baseConfig']['clue_recovery'] <= 0){
			return false;
		}
		$setDays = $this->config['baseConfig']['clue_recovery'];
		#1.找出没有沟通日志的，并且时间超过了线索或客户池回收周期
		$_7daysAgo = date('Y-m-d 23:59:59', strtotime("-{$setDays} days",time()));
		$datetime = date('Y-m-d H:i:s', time());

		$where[] = ['clue_log_number', '<=', 0];
		$where[] = ['is_public','=', 0];
		$where[] = ['update_time', '<=', $_7daysAgo];
        $res = ClueModel::where($where)->select();
        $count = count($res);
        if($count > 0){
        	try {
        		foreach ($res as $key => $value) {
        			$in[] = $value['id'];
        		}
        		$str = trim(implode(',', $in), ',');
        		$res = ClueModel::where('id', 'in', $in)->update(['is_public'=> 1, 'to_public_time'=> $datetime]);
        		debug('end');
        		if($res > 0){
        			$log = [
        				'create_time'=> $datetime,
        				'content'=>'处理ID为'.$str.',共'.$count.'条线索到线索池',
        				'result'=> 'success',
        				'type'=> 'clue_to_public',
        				'cost_time'=> debug('begin','end').'s'
        			];
        			CrontabLogModel::create($log);
        		}else{
        			$log = [
        				'create_time'=> $datetime,
        				'content'=>'处理ID为'.$str.',共'.$count.'条线索到线索池',
        				'result'=> 'fail',
        				'type'=> 'clue_to_public',
        				'cost_time'=> debug('begin','end').'s'
        			];
        			CrontabLogModel::create($log);
        		}
        	} catch (\Exception $e) {
        		trace($e->getMessage(),'error');
        	}
        }else{
        	debug('end');
        	$log = [
				'create_time'=> $datetime,
				'content'=>'处理0条线索信息到线索池',
				'result'=> 'success',
				'type'=> 'clue_to_public',
				'cost_time'=> debug('begin','end').'s'
			];
			CrontabLogModel::create($log);
        }
	}

	/**
	 * 客户移动到客户池
	 * @Author Foggy
	 * @Date   2018-11-22
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function customerBatchToPublic(CustomerModel $CustomerModel){
		debug('begin');
		if($this->config['baseConfig']['customer_recovery'] <= 0){
			return false;
		}
		$setDays = $this->config['baseConfig']['customer_recovery'];
		#1.找出没有沟通日志的，并且时间超过了客户池回收周期
		$_7daysAgo = date('Y-m-d 23:59:59', strtotime("-{$setDays} days",time()));
		$datetime = date('Y-m-d H:i:s', time());
		//dump($_7daysAgo);
		$where[] = ['customer_log_number', '<=', 0];
		$where[] = ['is_public','=', 0];
		$where[] = ['update_time', '<=', $_7daysAgo];
        $res = CustomerModel::where($where)->select();
        $count = count($res);
        if($count > 0){
        	try {
        		foreach ($res as $key => $value) {
        			$in[] = $value['id'];
        		}
        		$str = trim(implode(',', $in), ',');
        		$res = CustomerModel::where('id', 'in', $in)->update(['is_public'=> 1, 'to_public_time'=> $datetime]);
        		debug('end');
        		if($res > 0){
        			$log = [
        				'create_time'=> $datetime,
        				'content'=>'处理ID为'.$str.',共'.$count.'条客户到客户池',
        				'result'=> 'success',
        				'type'=> 'customer_to_public',
        				'cost_time'=> debug('begin','end').'s'
        			];
        			CrontabLogModel::create($log);
        		}else{
        			$log = [
        				'create_time'=> $datetime,
        				'content'=>'处理ID为'.$str.',共'.$count.'条客户到客户池',
        				'result'=> 'fail',
        				'type'=> 'customer_to_public',
        				'cost_time'=> debug('begin','end').'s'
        			];
        			CrontabLogModel::create($log);
        		}
        	} catch (\Exception $e) {
        		//dump($e->getErrorMsg);
        		trace($e->getMessage(),'error');
        	}
        }else{
        	debug('end');
        	$log = [
				'create_time'=> $datetime,
				'content'=>'处理0条客户信息到客户池',
				'result'=> 'success',
				'type'=> 'customer_to_public',
				'cost_time'=> debug('begin','end').'s'
			];
			CrontabLogModel::create($log);
        }
	}

	/**
	 * 定时给老板发送员工的操作日志
	 * @Author Foggy
	 * @Date   2018-11-28
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 * url:http://crm.linjoin.cn/index.php/crontab/crontab/sendbosslog
	 */
	public function sendBossLog(LogBossModel $LogBossModel){
		$boss = $LogBossModel::with('employee,boss')->select()->toArray();
		$count = count($boss);
		if($count > 0){
			foreach($boss as $key=>$value){
				$bossArray[$value['boss_user_id']][] = $value;
			}

			foreach ($bossArray as $key => $value) {
				foreach($value as $k=>$v){
					$toOpenid = $v['boss']['openid'];
					$data = [
						"first"  => ["收到一份工作日志\r\n", "#787AF6"],
			         	"keyword1"   => [$v['employee']['username']."\r\n", "#d22808"],
			         	"keyword2"  => date('Y-m-d H:i:s')."\r\n",
			         	"keyword3"  => [$v['employee']['username'].' 在 '.date('Y-m-d',time()).'的工作日志'."\r\n", '#099dda'],
			         	"remark" => "\r\n点击进入查看详情",
					];
					$url = url('index/log/read',['uid'=>$v['employee']['id'], 'date'=> date('Y-m-d',time())],'html',true);
					if($toOpenid){
						try {
							Wechat::templateMsg($toOpenid, $data, $url);
						} catch (\Exception $e) {
							trace($e->getMessage(), 'error');
						}
					}
				}
			}
		}
	}
}