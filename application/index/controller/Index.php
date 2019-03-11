<?php
namespace app\index\controller;
use app\facade\Wechat;
use app\facade\Auth;
use app\common\model\Clue as ClueModel;
use app\common\model\Users as UsersModel;
use app\common\model\Product as ProductModel;
use app\common\model\Customer as CustomerModel;
use app\common\model\Contract as ContractModel;

class Index extends Base
{
    public $user;

    public function __construct(){
        parent::__construct();
        $this->user = session('loginUser');
    }
    public function index()
    {
        #1.获取到待办事项。
        $startDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d', time())));
        $endData = date('Y-m-d H:i:s', strtotime(date("Y-m-d 23:59:59")));
        //今天需要联系的线索
        $schedule['clue'] = ClueModel::where('userid','=',$this->user['id'])->where('next_contact_time', 'between', [$startDate, $endData])->count();
        //今天需要联系的客户
        $schedule['customer'] = CustomerModel::where('userid','=',$this->user['id'])->where('next_contact_time', 'between', [$startDate, $endData])->count();
        //待我审核的合同
        $schedule['pending_contract'] = ContractModel::where('approvers_userid','=',$this->user['id'])->where('status','=',1)->count();
        $schedule['total'] = $schedule['clue'] + $schedule['customer'] + $schedule['pending_contract'];
        $this->assign('schedule', $schedule);
        return view();
    }

    /**
     * CRM主页数据分析
     * @Author Foggy
     * @Date   2018-12-03
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function panel(){
        #1.线索池信息
        $clue = [];
        //总线索数量
        $clue['total'] = ClueModel::count();
        //今日线索数量
        $clue['today'] = ClueModel::whereTime('create_time', 'today')->count();
        //本周线索数量
        $clue['week'] = ClueModel::whereTime('create_time', 'week')->count();
        //本月线索数量
        $clue['month'] = ClueModel::whereTime('create_time', 'month')->count();
        //本年线索数量
        $clue['year'] = ClueModel::whereTime('create_time', 'year')->count();
        //线索池人数
        $clue['public'] = ClueModel::where(['is_public'=> 1])->count();
        $this->assign('clue', $clue);

        #2.客户池信息
        $customer = [];
        //总客户数量
        $customer['total'] = CustomerModel::count();
        //今日客户数量
        $customer['today'] = CustomerModel::whereTime('create_time', 'today')->count();
        //本周客户数量
        $customer['week'] = CustomerModel::whereTime('create_time', 'week')->count();
        //本月客户数量
        $customer['month'] = CustomerModel::whereTime('create_time', 'month')->count();
        //本年客户数量
        $customer['year'] = CustomerModel::whereTime('create_time', 'year')->count();
        //客户池人数
        $customer['public'] = CustomerModel::where(['is_public'=> 1])->count();
        $this->assign('customer', $customer);

        #3.产品信息【未完待处理】
        $product = [];
        //总产品数量
        $product['total'] = ProductModel::count();
        //上架数量
        $product['up'] = ProductModel::where(['status'=> 1])->count();
        //下架数量
        $product['down'] = ProductModel::where(['status'=> 2])->count();
        $this->assign('product', $product);

        #4.合同信息
        $contract = [];
        //总金额
        $contract['total'] = ContractModel::sum('money');
        //今日金额
        $contract['today'] = ContractModel::whereTime('sign_time', 'today')->sum('money');
        //本周金额
        $contract['week'] = ContractModel::whereTime('sign_time', 'week')->sum('money');
        //本月金额
        $contract['month'] = ContractModel::whereTime('sign_time', 'month')->sum('money');
        //本年金额
        $contract['year'] = ContractModel::whereTime('sign_time', 'year')->sum('money');
        $this->assign('contract', $contract);

        #5.供应商
        $supplierModel = new \app\common\model\Supplier;
        $supplier = $supplierModel->count();
        $this->assign('supplier', $supplier);
        #6.收样
        $receiveModel = new \app\common\model\Sample;
        $receiveSample = $receiveModel->count();
        $this->assign('receiveSample', $receiveSample);
        #6.发样
        $sendModel = new \app\common\model\SendSample;
        $sendSample = $sendModel->count();
        $this->assign('sendSample', $sendSample);
        #7.员工数
        $users= UsersModel::count();
        $this->assign('users', $users);
        return view();
    }

    /**
     * chart1图标数据
     * @Author Foggy
     * @Date   2018-12-03
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function chart1(){
        #8.合同数量统计【曲线图】
        $chart1Data['beginYear'] = date('Y', strtotime('-1 year',time()));
        $chart1Data['endYear'] = date('Y', time());
        for ($i=1; $i <= 12 ; $i++) {
            $data['year'] = [$chart1Data['beginYear'], $chart1Data['endYear']];
            $date1 = $chart1Data['beginYear']."-".$i."-"."01";
            $theMonth1= getthemonth($date1);
            $data['data'][$chart1Data['beginYear']][] = ContractModel::whereBetweenTime('sign_time', $theMonth1[0], $theMonth1[1])->count();

            $date2 = $chart1Data['endYear']."-".$i."-"."01";
            $theMonth2= getthemonth($date2);
            $data['data'][$chart1Data['endYear']][] = ContractModel::whereBetweenTime('sign_time', $theMonth2[0], $theMonth2[1])->count();
        }
        return $data;
    }

    /**
     * 客户来源饼图
     * @Author Foggy
     * @Date   2018-12-03
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function chart2(){
        //获取到客户来源设置
        $customerFrom = get_options('customer_from');
        $i = 0;
        foreach($customerFrom as $key=>$value){
            $items[$i]['value'] = CustomerModel::where(['from_id'=>$key])->count();
            $items[$i]['name'] = $value;
            $i++;
        }
        return $items;
    }

    /**
     * chart3图标数据
     * @Author Foggy
     * @Date   2018-12-03
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function chart3(){
        #8.合同数量统计【曲线图】
        $chart1Data['beginYear'] = date('Y', strtotime('-1 year',time()));
        $chart1Data['endYear'] = date('Y', time());
        for ($i=1; $i <= 12 ; $i++) {
            $data['year'] = [$chart1Data['beginYear'], $chart1Data['endYear']];
            $date1 = $chart1Data['beginYear']."-".$i."-"."01";
            $theMonth1= getthemonth($date1);
            $data['data'][$chart1Data['beginYear']][] = ContractModel::whereBetweenTime('sign_time', $theMonth1[0], $theMonth1[1])->sum('money');

            $date2 = $chart1Data['endYear']."-".$i."-"."01";
            $theMonth2= getthemonth($date2);
            $data['data'][$chart1Data['endYear']][] = ContractModel::whereBetweenTime('sign_time', $theMonth2[0], $theMonth2[1])->sum('money');
        }
        return $data;
    }

    /**
     * 员工线索排行榜
     * @Author Foggy
     * @Date   2018-12-03
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function chart4(){
        $lists = UsersModel::withCount('clues')->order('clues_count','asc')->limit(10)->select();
        $data = [];
        if(count($lists) > 0){
            foreach ($lists as $key => $value) {
                $data['name'][$key] = $value['username'];
                $data['value'][$key] = $value['clues_count'];
            }
        }

        return $data;
    }

    /**
     * 员工客户排行榜
     * @Author Foggy
     * @Date   2018-12-03
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function chart5(){
        $lists = UsersModel::withCount('customers')->order('customers_count','asc')->limit(10)->select();
        $data = [];
        if(count($lists) > 0){
            foreach ($lists as $key => $value) {
                $data['name'][$key] = $value['username'];
                $data['value'][$key] = $value['customers_count'];
            }
        }

        return $data;
    }
}
