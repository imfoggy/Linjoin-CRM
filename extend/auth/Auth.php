<?php
namespace auth;

/**
 * 权限认证类
 * 功能特性：
 * 1，是对规则进行认证，不是对节点进行认证。用户可以把节点当作规则名称实现对节点进行认证。
 *      $auth=new Auth();  $auth->check('规则名称','用户id')
 * 2，可以同时对多条规则进行认证，并设置多条规则的关系（or或者and）
 *      $auth=new Auth();  $auth->check('规则1,规则2','用户id','and')
 *      第三个参数为and时表示，用户需要同时具有规则1和规则2的权限。 当第三个参数为or时，表示用户值需要具备其中一个条件即可。默认为or
 * 3，一个用户可以属于多个用户组(think_auth_group_access表 定义了用户所属用户组)。我们需要设置每个用户组拥有哪些规则(think_auth_group 定义了用户组权限)
 *
 * 4，支持规则表达式。
 *      在think_auth_rule 表中定义一条规则时，如果type为1， condition字段就可以定义规则表达式。 如定义{score}>5  and {score}<100  表示用户的分数在5-100之间时这条规则才会通过。
 */
/**字段type的说明，之前我一直不明白为什么设计这个，难道是类给验证规则分类，这么单纯的想法，
 * 后来我看了getAuthList方法体发现使用了这个type字段查询，这个type值是通过check()->赋值，
 * 那么数据表中默认为1，这里的默认值又是1，这不是多此一举吗，所以应该不是当然分类，
 * 设想一下，如果，我的规则表中type值设置为0会发生什么结果？
 * 顺着执行流程我发现，如果我在不修改方法体中的type值时，数据表中type为0就不用检测附件规则，我想这就是type字段的作用了。
 * 然后，我又发现这不对啊，type=0,整个规则都不会被检测，
 * 所以结论还是type字段就是 为了分类，并且检测时，默认为1，如果有不同分类需要给check()的type参数修改为该类型的值
 * 大写的尴尬！！！
 */
class Auth
{

    //默认配置
    protected $_config = array(
        'AUTH_ON'           => true, // 认证开关
        'AUTH_TYPE'         => 1, // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP'        => 'auth_group', // 用户组数据表名
        'AUTH_GROUP_ACCESS' => 'auth_group_access', // 用户-用户组关系表
        'AUTH_RULE'         => 'auth_rule', // 权限规则表
        'AUTH_USER'         => 'member', // 用户信息表
    );

    public function __construct()
    {
        $this->_config['prefix'] = config('database.prefix');
        //如果额外配置了auth参数，则重新配置
        if ( config('extra.auth'))
        {
            $this->_config = array_merge($this->_config,config('extra.auth'));
        }
    }

    /**
     * 检查权限
     * @param name string|array 需要验证的规则列表,支持逗号分隔的权限规则或索引数组
     * @param uid  int          认证用户的id
     * @param string mode       执行check的模式【主要是为了验证当url中存在参数时的情况，~~可能是为了区分其他方式的验证模式】
     * @param relation string   如果为 'or' 表示满足任一条规则即通过验证;如果为 'and'则表示需满足所有规则才能通过验证
     * @return boolean          通过验证返回true;失败返回false
     */
    public function check($name, $uid, $type = 1, $mode = 'url', $relation = 'or')
    {
        if (!$this->_config['AUTH_ON']) {
            return true;
        }

        $authList = $this->getAuthList($uid, $type); //获取用户需要验证的所有有效规则列表
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }

        $list = array(); //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = unserialize(strtolower(serialize($_REQUEST)));
        }

        foreach ($authList as $auth) {
            //替换规则中"?"之前的string为空，----》用于判断规则url是否存在参数
            $query = preg_replace('/^.+\?/U', '', $auth);
            //规则表中的规则如果存在参数，必须完全匹配【参数与值必须完全匹配】才会被记录，不存在参数，匹配则直接记录，【记录表示用户有权限】
            if ('url' == $mode && $query != $auth) {
                //把所有的参数string变为变量存储在$param数组中
                parse_str($query, $param); //解析规则中的param
                //匹配相同的参数【交集】【为了保证下面规则表中参数和请求的参数完全相同】
                $intersect = array_intersect_assoc($REQUEST, $param);
                //替换规则中"?"之后的string为空，----》获取规则url
                $auth      = preg_replace('/\?.*$/U', '', $auth);
                //判断规则表中的规则url(去除参数)是否存在验证的规则中，请求$_REQUEST数组与参数数组相同【即请求参数与规则表中参数完全相同】
                if (in_array($auth, $name) && $intersect == $param) {
                    //如果节点相符且url参数满足
                    $list[] = $auth;
                }
            } else if (in_array($auth, $name)) {
                $list[] = $auth;
            }
        }
        //默认是or选择，如果存在记录则表示正常匹配，用户有权限
        if ('or' == $relation and !empty($list)) {
            return true;
        }
        //以验证的规则为主体，计算与用户匹配的规则的差集
        $diff = array_diff($name, $list);
        //如果用户拥有的所有的规则中包含了所有的“被验证的规则”【即被验证的规则，与用户的所有规则没有差集】
        if ('and' == $relation and empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * 根据用户id获取用户组,返回值为数组
     * @param  uid int     用户id
     * @return array       用户所属的用户组 array(
     *     array('uid'=>'用户id','group_id'=>'用户组id','title'=>'用户组名称','rules'=>'用户组拥有的规则id,多个,号隔开'),
     *     ...)
     */
    public function getGroups($uid)
    {
        static $groups = array();
        if (isset($groups[$uid])) {
            return $groups[$uid];
        }

        $sql = "select uid,group_id,title,rules from ".$this->_config['prefix'].$this->_config['AUTH_GROUP_ACCESS']." a left join ".$this->_config['prefix'].$this->_config['AUTH_GROUP']." g on a.group_id=g.id where a.uid=".$uid." and g.status='1'";
        $user_groups = db()->query($sql);

        $groups[$uid] = $user_groups ?: array();
        return $groups[$uid];
    }

    /**
     * 获得权限列表
     * @param integer $uid  用户id
     * @param integer $type
     */
    protected function getAuthList($uid, $type)
    {
        static $_authList = array(); //保存用户验证通过的权限列表
        $t                = implode(',', (array) $type);
        if (isset($_authList[$uid . $t])) {
            return $_authList[$uid . $t];
        }
        if (2 == $this->_config['AUTH_TYPE'] && isset($_SESSION['_AUTH_LIST_' . $uid . $t])) {
            return $_SESSION['_AUTH_LIST_' . $uid . $t];
        }

        //读取用户所属用户组
        $groups = $this->getGroups($uid);
        $ids    = array(); //保存用户所属用户组设置的所有权限规则id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid . $t] = array();
            return array();
        }
        $map = [
            'id'=>$ids,
            'type'=>$type,
            'status'=>1
        ];


        //读取用户组所有权限规则
        $rules = db($this->_config['AUTH_RULE'])->where($map)->field('name,condition')->select();

        //循环规则，判断结果。
        $authList = array(); //
        foreach ($rules as $rule) {
            //判断是否有额外的规则条件
            if (!empty($rule['condition'])) {
                //根据condition进行验证
                $user = $this->getUserInfo($uid); //获取用户信息,一维数组
                //替换[rule表中的附加规则]如"{score}>10"为[用户信息表中的字段的值]如"$user['score']>10"
                //正则说明 "/\{(\w*?)\}/" => 匹配 "{score}" , 替换内容 中\\1表示匹配第一个(),
                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                //生成规则如：$user['score']>10 ；把字符串作为php代码实现
                @(eval('$condition=(' . $command . ');'));
                //符合条件就记录
                if ($condition) {
                    $authList[] = strtolower($rule['name']);
                }
            } else {
                //只要存在就记录
                $authList[] = strtolower($rule['name']);
            }
        }
        $_authList[$uid . $t] = $authList;
        if (2 == $this->_config['AUTH_TYPE']) {
            //规则列表结果保存到session
            $_SESSION['_AUTH_LIST_' . $uid . $t] = $authList;
        }
        return array_unique($authList);
    }

    /**
     * 获得用户资料,根据自己的情况读取数据库
     */
    protected function getUserInfo($uid)
    {
        static $userinfo = array();
        if (!isset($userinfo[$uid])) {
            $map['id'] = $uid;
            $userinfo[$uid] = db($this->_config['AUTH_USER'])->where($map)->find();
        }
        return $userinfo[$uid];
    }

}
