<?php
// 应用公共文件
use think\Facade;
/**
 * [门脸，用于处理类的静态调用]
 * @Author Foggy
 * @Date   2018-10-08
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @return NULL
 */
Facade::bind([
	'app\facade\Index' => 'app\index\controller\Index',
	'app\facade\Auth'  => '\auth\Auth',
	'app\facade\Wechat'=> 'app\weixin\controller\Weixin'
]);

/**
 * [apiSuccess description]
 * @Author Foggy
 * @Date   2018-10-09
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  string            $msg  [description]
 * @param  array             $data [description]
 * @return [type]                  [description]
 */
function apiSuccess($msg = '操作成功', $data = ''){
	return json(['code'=> 200, 'msg'=> $msg, 'data'=> $data]);
}

/**
 * [apiFail description]
 * @Author Foggy
 * @Date   2018-10-09
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  string            $msg  [description]
 * @param  array             $data [description]
 * @return [type]                  [description]
 */
function apiFail($msg = '操作失败', $data = ''){
	return json(['code'=> 400, 'msg'=> $msg, 'data'=> $data]);
}

/**
 * 生成a-z的随机数
 * @Author Foggy
 * @Date   2018-10-09
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $length [description]
 * @return [type]                    [description]
 */
function randomkeys($length = 6){
	$output = '';
	for ($a = 0; $a < $length; $a++) { 
		$output .= chr(mt_rand(33,126));
	}

	return $output;
}

/**
 * 加密密码
 * @Author Foggy
 * @Date   2018-10-09
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  string            $password [description]
 * @param  string            $crypt    [description]
 * @return [type]                      [description]
 */
function act_password($password = '', $crypt = 'abcdef'){
	$salt = config('safe.salt');
	return md5($salt.$password.$crypt);
}

/**
 * 获取options表中的数据
 * @Author Foggy
 * @Date   2018-10-10
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  string            $key [表中key字段]
 * @return [type]                 [description]
 */
function get_options($key = ''){
	$comId = session('loginUser.com_id');
	$string = db('options')->where(['key'=> $key, 'com_id'=> $comId])->find();
	if(!$string){
		return false;
	}
	if($string['value'] && $string['format']){
		if($string['format'] == 'string'){
			$partArray = explode(';', $string['value']);
			$result = [];
			//如果数组中有数据
			if($partArray[0]){
				foreach($partArray as $key=> $value){
					$ex = explode('-', $value);
					//如果count($ex) > 2 表示用户设置的value中包含'-'，需要做拼接处理
					if(count($ex) > 2){
						$param = '';
						for($i = 1; $i < count($ex); $i++){
							$param .= $ex[$i].'-';
						}
						$param = rtrim($param, '-');
						$result[$ex[0]] = $param;
					}else{
						$result[$ex[0]] = $ex[1];
					}
				}
			}
		}elseif($string['format'] == 'json'){
			$partArray = json_decode($string['value'], true);
			$result = $partArray;
		}
	}

	return $result;
}

/**
 * 设置options表中的配置信息
 * @Author Foggy
 * @Date   2018-11-05
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $key   [description]
 * @param  [type]            $value [description]
 */
function set_option($key, $value){
	$comId = session('loginUser.com_id');
	if(empty($key)){
		die('参数不能为空');
	}

	//检索信息
	$info = db('options')->where(['key'=> $key, 'com_id'=> $comId])->find();
	if(!$info){
		die('信息不存在');
	}

	//检测指定的key字段是否存在
	$result = db('options')->where(['key'=> $key, 'com_id'=> $comId])->update(['value'=> $value]);

	return $result;
}

/**
 * 无限分类树
 * @Author Foggy
 * @Date   2018-10-18
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  array             $item  [数据库里查询出来的数组]
 * @param  integer           $pid   [默认父级id字段]
 * @param  string            $sub   [下级数组的key名称]
 * @param  integer           $level [层级深度]
 * @return [type]                   [description]
 */
function category_trees($item = [],$pid = 0,$sub = '_child', $level = 1){
	$data = [];  
    foreach($item as $key=> $val){
        if($val['pid'] == $pid){
            $val['level'] = $level;
            $val[$sub] = category_trees($item, $val['id'], '_child', $level + 1);
            $data[] = $val;
        } 
    }

    return $data; 
}

/**
 * 产品无限分类treeHtml
 * @Author Foggy
 * @Date   2018-11-07
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $array [description]
 * @return [type]                   [description]
 */
function category_tree_html($array){
	if(!empty($array)){
		foreach($array as $key=>$value){
			//echo str_repeat('-', $value['level'] - 1).$value['name'].'<br>';
			$str .= '<li class="jstree-open" rel="'.$value['id'].'">';
			$str .= '<span data-categoryid="'.$value['id'].'" class="tree-href">'.$value['catname'].'</span>';
			if(!empty($value['_child'])){
				$str .= '<ul>';
				$str .= category_tree_html($value['_child']);
				$str .= '</ul>';
			}
			$str .= '</li>';
		}
	}

	return $str;
}

/**
 * |部门无限分类的html
 * @Author Foggy
 * @Date   2018-11-07
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $array [description]
 * @return [type]                   [description]
 */
function department_html($array){
	if(!empty($array)){
		foreach($array as $key=>$value){
			//echo str_repeat('-', $value['level'] - 1).$value['name'].'<br>';
			$str .= '<li class="jstree-open" rel="'.$value['id'].'">';
			$str .= '<span data-href="'.url("department/index",['department_id'=> $value['id']]).'" class="tree-href">'.$value['name'].'</span>';
			if(!empty($value['_child'])){
				$str .= '<ul>';
				$str .= department_html($value['_child']);
				$str .= '</ul>';
			}
			$str .= '<div class="btn-group slide-category"><button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle">设置 <span class="caret"></span></button><ul class="dropdown-menu"><li data-id="'.$value['id'].'" onclick="showCreateDepartment(this);"><a href="javascript:void(0)">新建下级部门</a></li><li data-id="'.$value['id'].'" onclick=showCreateDepartment(this,"edit");><a href="buttons.html#">修改部门</a></li><li data-id="'.$value['id'].'" onclick=deleteDepartment(this);><a href="buttons.html#">删除部门</a></li><li data-id="'.$value['id'].'" onclick=showCreateGroup(this);><a href="buttons.html#">新增岗位</a></li></ul></div>';
			$str .= '</li>';
		}
	}

	return $str;
}

/**
 * 产品无限分类的html
 * @Author Foggy
 * @Date   2018-11-07
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $array [description]
 * @return [type]                   [description]
 */
function category_html($array){
	$str = '';
	if(!empty($array)){
		foreach($array as $key=>$value){
			$str .=  '<option value="'.$value['id'].'">'.str_repeat('--', $value['level'] - 1).$value['catname'].'</option>';
			if(!empty($value['_child'])){
				$str .= category_html($value['_child']);
			}
		}
	}

	return $str;
}

/**
 * 部门无限分类html<option>
 * @Author Foggy
 * @Date   2018-11-07
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $array [description]
 * @return [type]                   [description]
 */
function department_option_html($array){
	$str = '';
	if(!empty($array)){
		foreach($array as $key=>$value){
			$str .=  '<option value="'.$value['id'].'">'.str_repeat('--', $value['level'] - 1).$value['name'].'</option>';
			if(!empty($value['_child'])){
				$str .= department_option_html($value['_child']);
			}
		}
	}

	return $str;
}

/**
 * |岗位无限分类的html
 * @Author Foggy
 * @Date   2018-11-07
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $array [description]
 * @return [type]                   [description]
 */
function group_html($array){
	if(!empty($array)){
		foreach($array as $key=>$value){
			//echo str_repeat('-', $value['level'] - 1).$value['name'].'<br>';
			$str .= '<li class="jstree-open" rel="'.$value['id'].'">';
			$str .= '<span>'.$value['title'].'- [ 所属部门：'.$value['department']['name'].']'.' - ['. $value['users_count'].'名员工 ]'.'</span>';
			if(!empty($value['_child'])){
				$str .= '<ul>';
				$str .= group_html($value['_child']);
				$str .= '</ul>';
			}
			$str .= '<div class="btn-group slide-category"><button data-toggle="dropdown" class="btn btn-default btn-xs dropdown-toggle">设置 <span class="caret"></span></button><ul class="dropdown-menu"><li data-id="'.$value['id'].'" onclick=showEditGroup(this);><a href="buttons.html#">修改职位</a></li><li data-id="'.$value['id'].'" onclick="deleteGroup(this);"><a href="buttons.html#">删除职位</a></li><li data-id="'.$value['id'].'" onclick="showCreateUser(this);"><a href="javascript:void(0)">添加用户</a></li><li data-id="'.$value['id'].'" onclick="showAuthorize(this);"><a href="javascript:void(0)">职位授权</a></li></ul></div>';
			$str .= '</li>';
		}
	}

	return $str;
}

/**
 * 职位无限分类html<option>
 * @Author Foggy
 * @Date   2018-11-07
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $array [description]
 * @return [type]                   [description]
 */
function group_option_html($array){
	$str = '';
	if(!empty($array)){
		foreach($array as $key=>$value){
			$str .=  '<option value="'.$value['id'].'">'.str_repeat('--', $value['level'] - 1).$value['title'].'</option>';
			if(!empty($value['_child'])){
				$str .= group_option_html($value['_child']);
			}
		}
	}

	return $str;
}

/**
 * 分类列表页面html
 * @Author Foggy
 * @Date   2018-11-07
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $array [description]
 * @return [type]                   [description]
 */
function category_list_html($array){
	$str = '';
	if(!empty($array)){
		foreach($array as $key=>$value){
			$repeat = str_repeat('--', $value['level'] - 1);
			$str .= <<<EOF
			<tr>
				<td style="text-align:center;" nowrap="nowrap">
					<div class="checkbox checkbox-primary">
						<input name="id[]" class="check_list" type="checkbox" value="{$value['id']}">
						<label for=""></label>
					</div>
				</td>
				<td nowrap="nowrap">{$repeat}{$value['catname']}</td>
				<td nowrap="nowrap">
					<a class="edit" href="javascript:void(0)" onclick="showForm({$value['id']}, 'edit');" rel="{$value['id']}">编辑</a>&nbsp;&nbsp;
					<a class="delete" href="javascript:void(0)" onclick="del({$value['id']});" rel="{$value['id']}">删除</a>
				</td>
			</tr>
EOF;
			if(!empty($value['_child'])){
				$str .= category_list_html($value['_child']);
			}
		}
	}

	return $str;
}

/**
 * 递归检测当前groupid下的所有职位id
 * @Author Foggy
 * @Date   2018-11-13
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $groupid [description]
 * @return [type]                     [description]
 */
function group_users_check($groupid){
	static $ids = [];
	if(!in_array($groupid, $ids)){
		$ids[] = (int)$groupid;
	}
	$_AUTH_GROUP_DB = db('auth_group');
	$id = $_AUTH_GROUP_DB->where(['pid'=> $groupid])->column('id');
	if($id){
		foreach($id as $key=>$value){
			group_users_check($value);
		}
	}
	return $ids;
}

/**
 * 递归检测当前departmentid下的所有部门id
 * @Author Foggy
 * @Date   2018-11-13
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $departmentid [description]
 * @return [type]                     [description]
 */
function department_users_check($departmentid){
	static $ids = [];
	if(!in_array($departmentid, $ids)){
		$ids[] = (int)$departmentid;
	}
	$_DEPARTMENT_DB = db('department');
	$id = $_DEPARTMENT_DB->where(['pid'=> $departmentid])->column('id');
	if($id){
		foreach($id as $key=>$value){
			department_users_check($value);
		}
	}
	// if(count($ids) > 0){
	// 	unset($ids[0]);
	// }
	
	return $ids;
}

/**
 * 笛卡尔积
 * @Author Foggy
 * @Date   2018-11-17
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $data [description]
 * @return [type]                  [description]
 */
/*
$skuattr = Array
(
    '7'  => Array
    (
        '6' => '22x33',
        '9' => '44x55',
    ),

    '8'  => Array
    (
        '12' => '大小号'
    ),

    '9'  => Array
    (
        '8'  => '金属质',
        '13' => '塑料',
    ),

    '16' => Array
    (
        '14' => '圆形'
    )

);
*/
function combineDika($data) {
    $result = array();
    foreach (array_shift($data) as $k=>$item) {
        $result[] = array($k=>$item);
    }


    foreach ($data as $k => $v) {
        $result2 = [];
        foreach ($result as $k1=>$item1) {
            foreach ($v as $k2=>$item2) {
                $temp     = $item1;
                $temp[$k2]   = $item2;
                $result2[] = $temp;
            }
        }
        $result = $result2;
    }
    
    return $result;
}


/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays($day1, $day2)
{
  $second1 = strtotime($day1);
  $second2 = strtotime($day2);
   
  // if ($second1 < $second2) {
  //   $tmp = $second2;
  //   $second2 = $second1;
  //   $second1 = $tmp;
  // }
  return ($second1 - $second2) / 86400;
}

/**
 * 获取到某个职位的所有下级职位
 * @Author Foggy
 * @Date   2018-11-30
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  integer           $groupid [description]
 * @return [type]                     [description]
 */
function child_group($groupid = 0){
	$arr = group_users_check($groupid);
	if(count($arr) > 0){
		unset($arr[0]);
	}

	return $arr;
}

/**
 * 获取到职位下所有下级用户
 * @Author Foggy
 * @Date   2018-11-30
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  array             $groupids [description]
 * @return [type]                      [description]
 */
function child_group_users($groupid = 0){
	$groupids = child_group($groupid);
	if(!$groupids){
		return ['error'=> 'groupids参数错误'];
	}
	$arr = [];
	$arr = db('users')->where('group_id', 'in', $groupids)->column('id');
	return $arr;
}

/**
 * 获取到某个月份的第一天和最后一天
 * @Author Foggy
 * @Date   2018-12-03
 * @WeChat [vita_hacker]
 * @Email  [x_foggy@163.com]
 * @param  [type]            $date [description]
 * @return [type]                  [description]
 */
function getthemonth($date){
    $firstday = date('Y-m-01', strtotime($date));
    $lastday = date('Y-m-d', strtotime("$firstday +1 month -1 day"));
    return array($firstday,$lastday);
}