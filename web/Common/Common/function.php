<?php

// OneThink常量定义
const ONETHINK_VERSION    = '1.0.131218';
const ONETHINK_ADDON_PATH = './Addons/';


/**
 * 功能:发送验证码到手机
 * @param  $mobile  手机号码
 * @param  $code  	验证码
 * @return string  发送状态码
 */
function send_sms($mobile,$code){

	header("Content-Type: text/html; charset=UTF-8");

	vendor('wujing.send_phone_sms');

	$send_sms = new send_phone_sms($mobile,$code);

	$statusCode = $send_sms -> send_phone_yimei();

	return $statusCode[0];
}

	function exportExcel($data,$savefile=null,$title=null,$sheet){
        //若没有指定文件名则为当前时间戳
        if(is_null($savefile)){
            $savefile=time();
        }
        //若指字了excel表头，则把表单追加到正文内容前面去
        //if(is_array($title)){
        //    array_unshift($data,$title);
        //}
        Vendor('PHPExcel.PHPExcel');
        Vendor('PHPExcel.PHPExcel.IOFactory');
        Vendor('PHPExcel.PHPExcel.Reader.Excel5');
        $objPHPExcel = new PHPExcel();
       
        //print_R($data);print_r($sheet);exit;
        foreach($data as $k => $v){
        	$obj=$objPHPExcel->getSheet($k); 
        	$obj->getColumnDimension('B')->setWidth(15);
        	if(strstr($savefile, '悟净机')){
        		$obj->getColumnDimension('F')->setWidth(20);
        	}else{
        		$obj->getColumnDimension('E')->setWidth(20);
        	}
        	array_unshift($v,$title);   	
        	foreach ($v as $t => $vv){	            
	            $row=$t+1;//行
	            $nn=0;
	            foreach($vv as $vvv){
	                $col=chr(65+$nn);//列
	                $obj->setCellValue($col.$row,$vvv);//列,行,值
	                $nn++;
	            }
        	}
        	$objPHPExcel->getSheet($k)->setTitle("$sheet[$k]");
        	$objPHPExcel->createSheet();
        }
       
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$savefile.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

/**
 * Created by wujianke
 * Date: 14-8-13
 * 功能:加密解密字符串，目前用户cooike登录
 * @param  $string  	要加密的字符串
 * @param  $operation  	加密或者解密选项
 * @return string
 */
function uch_authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;	// 随机密钥长度 取值 0-32;
	// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	// 当此值为 0 时，则不产生随机密钥
	if(!defined('DATA_AUTH_KEY'))
		define('DATA_AUTH_KEY', '*****************');
	$key = md5($key ? $key : DATA_AUTH_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;

	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 * @author wujianke
 */
function is_login(){
	$user = session('user_auth');
	if (empty($user)) {
		$cUser=get_user_by_cookie();
		if(is_array($cUser))
		{
			if(is_pass_auth($cUser))
			{
				$auth = array(
					'uid'             => $cUser['uid'],
					'username'        => $cUser['username'],
				);
				//写一次session
				session('user_auth', $auth);
				session('user_auth_sign', data_auth_sign($auth));
				return $cUser['uid'];
			}
		}
		return 0;
		//return 0;
	} else {		
		return session('user_auth_sign') == data_auth_sign($user) ? $user['uid'] : 0;
		
	}
}

function is_administrator($uid = null){
	$uid = is_null($uid) ? is_login() : $uid;
	return $uid && (intval($uid) === C('USER_ADMINISTRATOR'));
}

function get_user_by_cookie()
{
	if (isset($_COOKIE['user']))
	{
		$userCookie=$_COOKIE['user'];
		$userArr=unserialize($userCookie);
		$userArr['token']=unserialize(uch_authcode($userArr['token'],'DECODE'));
		return $userArr;
	}
}

function data_auth_sign($data) {
	//数据类型检测
	if(!is_array($data)){
		$data = (array)$data;
	}
	ksort($data); //排序
	$code = http_build_query($data); //url编码并生成query字符串
	$sign = sha1($code); //生成签名
	return $sign;
}

function is_pass_auth($user)
{
	if($user['uid']==$user['token']['uid'])
	{
		$Api = new User\Api\UserApi();
		$info = $Api->info($user['uid']);
		if($user['token']['pwd']==$info[4])
		{
			return true;
		}
	}
	return false;
}

function tp_page($num,$count,$limit,$parameter,$url='')
{
	$Page = new \Think\Page($count,$limit,$parameter);
	$Page->rollPage = $num;
	$Page ->setConfig(next, "下一页");
	$Page ->setConfig(prev, "上一页");
	return $Page->show($url);
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 */
function api($name,$vars=array()){
	$array     = explode('/',$name);
	$method    = array_pop($array);
	$classname = array_pop($array);
	$module    = $array? array_pop($array) : 'Common';
	$callback  = $module.'\\Api\\'.$classname.'Api::'.$method;

	if(is_string($vars)) {
		parse_str($vars,$vars);
	}
	return call_user_func_array($callback,$vars);
}

/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array();
	if(is_array($list)) {
		// 创建基于主键的数组引用
		$refer = array();
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] =& $list[$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId =  $data[$pid];
			if ($root == $parentId) {
				$tree[] =& $list[$key];
			}else{
				if (isset($refer[$parentId])) {
					$parent =& $refer[$parentId];
					$parent[$child][] =& $list[$key];
				}
			}
		}
	}
	return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
	if(is_array($tree)) {
		$refer = array();
		foreach ($tree as $key => $value) {
			$reffer = $value;
			if(isset($reffer[$child])){
				unset($reffer[$child]);
				tree_to_list($value[$child], $child, $order, $list);
			}
			$list[] = $reffer;
		}
		$list = list_sort_by($list, $order, $sortby='asc');
	}
	return $list;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function format_bytes($size, $delimiter = '') {
	$units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 记录行为日志，并执行该行为的规则
 * @param string $action 行为标识
 * @param string $model 触发行为的模型名
 * @param int $record_id 触发行为的记录id
 * @param int $user_id 执行行为的用户id
 * @return boolean
 * @author huajie <banhuajie@163.com>
 */
function action_log($action = null, $model = null, $record_id = null, $user_id = null){

	//参数检查
	if(empty($action) || empty($model) || empty($record_id)){
		return '参数不能为空';
	}
	if(empty($user_id)){
		$user_id = is_login();
	}

	//查询行为,判断是否执行
	$action_info = M('Action')->getByName($action);
	if($action_info['status'] != 1){
		return '该行为被禁用或删除';
	}

	//插入行为日志
	$data['action_id']      =   $action_info['id'];
	$data['user_id']        =   $user_id;
	$data['action_ip']      =   ip2long(get_client_ip(0,1));
	$data['model']          =   $model;
	$data['record_id']      =   $record_id;
	$data['create_time']    =   NOW_TIME;

	//解析日志规则,生成日志备注
	if(!empty($action_info['log'])){
		if(preg_match_all('/\[(\S+?)\]/', $action_info['log'], $match)){
			$log['user']    =   $user_id;
			$log['record']  =   $record_id;
			$log['model']   =   $model;
			$log['time']    =   NOW_TIME;
			$log['data']    =   array('user'=>$user_id,'model'=>$model,'record'=>$record_id,'time'=>NOW_TIME);
			foreach ($match[1] as $value){
				$param = explode('|', $value);
				if(isset($param[1])){
					$replace[] = call_user_func($param[1],$log[$param[0]]);
				}else{
					$replace[] = $log[$param[0]];
				}
			}
			$data['remark'] =   str_replace($match[0], $replace, $action_info['log']);
		}else{
			$data['remark'] =   $action_info['log'];
		}
	}else{
		//未定义日志规则，记录操作url
		$data['remark']     =   '操作url：'.$_SERVER['REQUEST_URI'];
	}

	M('ActionLog')->add($data);

	if(!empty($action_info['rule'])){
		//解析行为
		$rules = parse_action($action, $user_id);

		//执行行为
		$res = execute_action($rules, $action_info['id'], $user_id);
	}
}

/**
 * 解析行为规则
 * 规则定义  table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
 * 规则字段解释：table->要操作的数据表，不需要加表前缀；
 *              field->要操作的字段；
 *              condition->操作的条件，目前支持字符串，默认变量{$self}为执行行为的用户
 *              rule->对字段进行的具体操作，目前支持四则混合运算，如：1+score*2/2-3
 *              cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次
 *              max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）
 * 单个行为后可加 ； 连接其他规则
 * @param string $action 行为id或者name
 * @param int $self 替换规则里的变量为执行用户的id
 * @return boolean|array: false解析出错 ， 成功返回规则数组
 * @author huajie <banhuajie@163.com>
 */
function parse_action($action = null, $self){
	if(empty($action)){
		return false;
	}

	//参数支持id或者name
	if(is_numeric($action)){
		$map = array('id'=>$action);
	}else{
		$map = array('name'=>$action);
	}

	//查询行为信息
	$info = M('Action')->where($map)->find();
	if(!$info || $info['status'] != 1){
		return false;
	}

	//解析规则:table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max][;......]
	$rules = $info['rule'];
	$rules = str_replace('{$self}', $self, $rules);
	$rules = explode(';', $rules);
	$return = array();
	foreach ($rules as $key=>&$rule){
		$rule = explode('|', $rule);
		foreach ($rule as $k=>$fields){
			$field = empty($fields) ? array() : explode(':', $fields);
			if(!empty($field)){
				$return[$key][$field[0]] = $field[1];
			}
		}
		//cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
		if(!array_key_exists('cycle', $return[$key]) || !array_key_exists('max', $return[$key])){
			unset($return[$key]['cycle'],$return[$key]['max']);
		}
	}

	return $return;
}

/**
 * 执行行为
 * @param array $rules 解析后的规则数组
 * @param int $action_id 行为id
 * @param array $user_id 执行的用户id
 * @return boolean false 失败 ， true 成功
 * @author huajie <banhuajie@163.com>
 */
function execute_action($rules = false, $action_id = null, $user_id = null){
	if(!$rules || empty($action_id) || empty($user_id)){
		return false;
	}

	$return = true;
	foreach ($rules as $rule){

		//检查执行周期
		$map = array('action_id'=>$action_id, 'user_id'=>$user_id);
		$map['create_time'] = array('gt', NOW_TIME - intval($rule['cycle']) * 3600);
		$exec_count = M('ActionLog')->where($map)->count();
		if($exec_count > $rule['max']){
			continue;
		}

		//执行数据库操作
		$Model = M(ucfirst($rule['table']));
		$field = $rule['field'];
		$res = $Model->where($rule['condition'])->setField($field, array('exp', $rule['rule']));

		if(!$res){
			$return = false;
		}
	}
	return $return;
}


/**
 * 获取文档封面图片
 * @param int $cover_id
 * @param string $field
 * @return 完整的数据  或者  指定的$field字段值
 * @author huajie <banhuajie@163.com>
 */
function get_cover($cover_id, $field = null){
    if(empty($cover_id)){
        return false;
    }
    $picture = M('Picture')->where(array('status'=>1))->getById($cover_id);
    if($field == 'path'){
        if(!empty($picture['url'])){
            $picture['path'] = $picture['url'];
        }else{
            $picture['path'] = __ROOT__.$picture['path'];
        }
    }
    return empty($field) ? $picture : $picture[$field];
}

