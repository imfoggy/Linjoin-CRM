<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Users as UsersModel;
use app\common\model\Customer as CustomerModel;
use app\common\model\CustomerLog as CustomerLogModel;
use app\common\model\CustomerContact as CustomerContactModel;
/**
 * 创建客户的RESTFul资源控制器
 */
class Customer extends Base
{
    use \app\traits\controller\Mytraits;

    public $user;
    public $config;

    protected $beforeActionList = [
        'country'=> ['only'=> 'create,edit'],
        'users'=> ['only'=> 'create,edit']
    ];

    public function __construct(){
        parent::__construct();
        $this->user = session('loginUser');

        $config = [];
        $companyProperty = get_options('customer_company_property');
        $companyStatus = get_options('customer_status');
        $companyIndustry = get_options('customer_industry');
        $companyFrom = get_options('customer_from');
        $companyEmployee = get_options('customer_employee');
        //获取跟进类型
        $followUp = get_options('follow_up');
        $this->config = [
            'property'=> $companyProperty,
            'status'=> $companyStatus,
            'industry'=> $companyIndustry,
            'from'=> $companyFrom,
            'employee'=> $companyEmployee,
            'followUp'=> $followUp,
            'position'=> get_options('positions_config'),
            'customer_contact_role'=> get_options('customer_contact_role')
        ];

        $this->assign('_config', $this->config);
    }

    /**
     * 获取到国家列表
     * @Author Foggy
     * @Date   2018-11-26
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function country(){
        $countries = db('countries')->select();
        $this->assign('countries', $countries);
    }

    /**
     * 获取到员工信息
     * @Author Foggy
     * @Date   2018-11-29
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function users(){
        //获取到供应商列表
        $users = UsersModel::select();
        $this->assign('users', $users);
    }
    /**
     * 显示客户列表
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function index()
    {
        $by = input('by', '');
        $startDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d', time())));
        $endData = date('Y-m-d H:i:s', strtotime(date("Y-m-d 23:59:59")));
        if($by == 'public'){
            $where[] = ['is_public','=',1];
        }elseif($by == 'need_contact'){
            $where[] = ['next_contact_time', 'between', [$startDate, $endData]];
        }else{
            $where[] = ['is_public','=',0];
        }
        $username = input('username', '');
        if($username){
            $where[] = ['company','like','%'.$username.'%'];
            $this->assign('username', $username);
        }
        $level = input('level', '');
        if($level == 'child'){
            $users = child_group_users($this->user['group_id']);
            $where[] = ['userid', 'in', $users];
            $this->assign('level', 'child');
        }elseif($level == 'my'){
            //$where[] = ['userid', '=', $this->user['group_id']];
            $where[] = ['userid', '=', $this->user['id']];
            $this->assign('level', 'my');
        }else{
            $where[] = ['userid', '=', $this->user['id']];
            $this->assign('level',"");
        }
        $list = CustomerModel::getCustomers($where);
        $this->assign('list', $list);
        $this->assign('by', $by);
        return view();
    }

    /**
     * 显示创建客户表单页.
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function create(Request $request)
    {
        $data = htmlspecialchars_decode(input('data'));
        $data = json_decode($data,true);
        if($data){
            #说明数据来源是线索转换
            $this->assign('data', $data);
        }

        $sn = $this->cmsn();
        $this->assign('sn', $sn);
        return view();
    }

    /**
     * 生成客户编号
     * @Author Foggy
     * @Date   2018-12-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function cmsn(){
        //自动生成客户编号
        $prefix = 'CM';
        $date = date('Ymd',time());
        $random = mt_rand(10000,99999);
        $sn = $prefix.$date.$random;
        return $sn;
    }

    /**
     * 保存新建的客户
     * @Author Foggy
     * @Date   2018-10-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  Request           $request   [description]
     * @param  CustomerModel         $customerModel [description]
     * @return [type]                       [description]
     */
    public function save(Request $request, CustomerModel $CustomerModel)
    {
        $data = input('post.');
        $data['create_userid'] = $this->user['id'];
        $data['userid'] = $this->user['id'];
        $result = $this->validate($data, 'app\index\validate\Customer.create');
        if(true !== $result){
            $this->error($result);
        }
        if($CustomerModel->allowField(true)->save($data)){
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'增加了一个新客户，公司名称是【'.$data['company'].'】');
            $this->success('操作成功', url('index'));
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 显示指定的客户
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $id [description]
     * @return [type]                [description]
     */
    public function read($id, CustomerModel $CustomerModel)
    {
        $info = $CustomerModel->getCustomerInfoById($id);
        $this->assign('info', $info);
        return view();
    }

    /**
     * 显示编辑客户表单页.
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $id [description]
     * @return [type]                [description]
     */
    public function edit($id, CustomerModel $CustomerModel)
    {
        $info = $CustomerModel->getCustomerInfoById($id);
        $this->assign('info', $info);
        return view();
    }

    /**
     * 保存更新的客户
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  Request           $request [description]
     * @param  [type]            $id      [description]
     * @return [type]                     [description]
     */
    public function update($id, Request $request)
    {
        if(!$id){
            $this->error('客户不存在');
        }
        $post = array_filter(input('post.'));

        $result = CustomerModel::update($post, ['id'=> $id]);
        if($result > 0){
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'更新了一个ID为'.$id.'的客户信息');
            return ['code'=> 1, 'msg'=> '操作成功'];
        }else{
            return ['code'=> 0, 'msg'=> '操作失败'];
        }
    }

    /**
     * 删除指定客户
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $id [description]
     * @return [type]                [description]
     */
    public function delete($id)
    {
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

        if(CustomerModel::destroy($str)){
            CustomerLogModel::destroy(['customer_id'=> $id]);
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除了ID为'.$str.'的客户信息');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 删除客户里面的沟通日志
     * @Author Foggy
     * @Date   2018-10-13
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $id [description]
     * @return [type]                [description]
     */
    public function deleteLog($id){
        if(!$id){
            $this->error('缺少id参数');
        }

        if(CustomerLogModel::destroy($id)){
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'删除了客户沟通日志');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 增加客户沟通日志
     * @Author Foggy
     * @Date   2018-10-16
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function createlog(CustomerLogModel $CustomerLogModel){
        $data = input('post.');
        $data['userid'] = $this->user['id'];
        $CustomerLogModel->allowField(true)->save($data);
        if($CustomerLogModel->id > 0){
            //关联新增
            if(!empty($data['file'])){
                $CustomerLogModel = CustomerLogModel::get($CustomerLogModel->id);
                $CustomerLogModel->file()->save($data);
            }
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'增加了客户沟通日志');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 客户移动到客户池
     * @Author Foggy
     * @Date   2018-10-16
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function toPublic(){
        $ids = input('id/a');
        if(empty($ids)){
            $this->error('请至少选中一条信息');
        }
        $datetime = date('Y-m-d H:i:s',time());
        $result = CustomerModel::where('id','in', $ids)->update(['is_public'=> 1,'to_public_time'=> $datetime,'userid'=> 0]);
        $str = implode(',', $ids);
        if($result > 0){
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'将ID为'.$str.'的客户放入了客户池');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 客户池批量领取
     * @Author Foggy
     * @Date   2018-10-17
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function toReceive(){
        $ids = input('id/a');
        if(empty($ids)){
            $this->error('请至少选中一条信息');
        }

        $result = CustomerModel::where('id','in', $ids)->update(['is_public'=> 0, 'userid'=> $this->user['id']]);

        if($result > 0){
            $str = implode(',', $ids);
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'从客户池领取了ID为'.$str.'的客户');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 获取到客户下面的联系人信息
     * @Author Foggy
     * @Date   2018-10-25
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function contacts(){
        $customerId = input('post.customerId', 0);
        if($customerId <= 0){
            $this->error('客户id错误');
        }

        $result = CustomerContactModel::getContacts(['customer_id'=> $customerId]);
        $this->success('获取成功',$_SERVER["HTTP_REFERER"], $result);
    }
}
