<?php
namespace app\index\controller;
use think\Request;
use app\common\model\Clue as ClueModel;
use app\common\model\ClueLog as ClueLogModel;
/**
 * 创建线索的RESTFul资源控制器
 */
class Clue extends Base
{
    use \app\traits\controller\Mytraits;

    public $user;

    /**
     * 前置操作
     * @var [type]
     */
    protected $beforeActionList = [
        'country'=> ['only'=> 'create,edit'],
    ];

    public function __construct(){
        parent::__construct();
        $this->user = session('loginUser');
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
     * 显示线索列表
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
            $where[] = ['name','like','%'.$username.'%'];
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

        $list = ClueModel::getClues($where);
        //获取跟进类型
        $followUp = get_options('follow_up');
        $this->assign('followUp', $followUp);
        $this->assign('list', $list);
        $this->assign('by', $by);
        return view();
    }

    /**
     * 显示创建线索表单页.
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function create()
    {
        //线索客户来源
        $clueFrom = get_options('clue_from');
        $positions = get_options('positions_config');
        $this->assign('positions', $positions);
        $this->assign('clueFrom', $clueFrom);
        return view();
    }

    /**
     * 保存新建的线索
     * @Author Foggy
     * @Date   2018-10-11
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  Request           $request   [description]
     * @param  ClueModel         $culeModel [description]
     * @return [type]                       [description]
     */
    public function save(Request $request, ClueModel $clueModel)
    {
        $data = input('post.');
        $data['userid'] = $this->user['id'];
        $data['create_userid'] = $this->user['id'];
        $result = $this->validate($data, 'app\index\validate\Clue.create');
        if(true !== $result){
            $this->error($result);
        }
        if($clueModel->allowField(true)->save($data)){
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'新建了一条新的线索，线索ID为'.$clueModel->id.',线索人姓名是['.$data['name'].']');
            $this->success('操作成功', url('index'));
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 显示指定的线索
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $id [description]
     * @return [type]                [description]
     */
    public function read($id, ClueModel $clueModel)
    {
        $info = $clueModel->getClueInfoById($id);
        $this->assign('info', $info);
        $clueFrom = get_options('clue_from');
        $this->assign('clueFrom',$clueFrom);
        $positions = get_options('positions_config');
        $this->assign('positions', $positions);
        return view();
    }

    /**
     * 显示编辑线索表单页.
     * @Author Foggy
     * @Date   2018-10-10
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @param  [type]            $id [description]
     * @return [type]                [description]
     */
    public function edit($id, ClueModel $clueModel)
    {
        $info = $clueModel->getClueInfoById($id);
        $this->assign('info', $info);
        $clueFrom = get_options('clue_from');
        $this->assign('clueFrom',$clueFrom);
        $positions = get_options('positions_config');
        $this->assign('positions', $positions);
        return view();
    }

    /**
     * 保存更新的线索
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
            $this->error('线索不存在');
        }
        $post = array_filter(input('post.'));

        $result = ClueModel::update($post, ['id'=> $id]);
        if($result > 0){
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'修改了一条线索，线索ID为'.$id.',线索人姓名是['.$post['name'].']');
            return ['code'=> 1, 'msg'=> '操作成功'];
        }else{
            return ['code'=> 0, 'msg'=> '操作失败'];
        }
    }

    /**
     * 删除指定线索
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

        if(ClueModel::destroy($str)){
            ClueLogModel::destroy(['clue_id'=> $id]);
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除了ID为'.$str.'的线索');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 删除线索里面的沟通日志
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

        if(ClueLogModel::destroy($id)){
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'删除了ID为'.$id.'的线索沟通日志');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 增加线索沟通日志
     * @Author Foggy
     * @Date   2018-10-16
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function createlog(ClueLogModel $ClueLogModel){
        $data = input('post.');
        $data['userid'] = $this->user['id'];
        $ClueLogModel->allowField(true)->save($data);
        if($ClueLogModel->id > 0){
            ClueModel::where(['id'=>$data['clue_id']])->inc('clue_log_number');
            //关联新增
            if(!empty($data['file'])){
                $ClueLogModel = ClueLogModel::get($ClueLogModel->id);
                $ClueLogModel->file()->save($data);
            }
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'增加了ID为'.$ClueLogModel->id.'的线索沟通日志');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 线索移动到线索池
     * @Author Foggy
     * @Date   2018-10-16
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function toPublic(){
        $ids = input('id/a');
        if(empty($ids)){
            $this->error('请至少选中一条线索');
        }
        $datetime = date('Y-m-d H:i:s',time());
        $result = ClueModel::where('id','in', $ids)->update(['is_public'=> 1, 'to_public_time'=> $datetime, 'userid'=> 0]);

        if($result > 0){
            $ids = implode(',', $ids);
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'把ID为'.$ids.'的线索放入了线索池');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 线索池批量领取
     * @Author Foggy
     * @Date   2018-10-17
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function toReceive(){
        $ids = input('id/a');
        if(empty($ids)){
            $this->error('请至少选中一条线索');
        }

        $result = ClueModel::where('id','in', $ids)->update(['is_public'=> 0, 'userid'=> $this->user['id']]);

        if($result > 0){
            $ids = implode(',', $ids);
            self::writeLog($this->user['id'], '用户'.$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'从线索池中领取了ID为'.$ids.'的线索');
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 转为客户
     * @Author Foggy
     * @Date   2018-11-29
     * @WeChat [vita_hacker]
     * @Email  [x_foggy@163.com]
     * @return [type]            [description]
     */
    public function tocustomer(Request $request){
        $id = request()->id;
        $info = ClueModel::get($id);
        $this->redirect('customer/create', ['data'=> $info]);
    }
}