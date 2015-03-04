<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace User\Model;
use Think\Model;
/**
 * 会员模型
 */
class UserModel extends Model{
	/**
	 * 数据表前缀
	 * @var string
	 */
	protected $tablePrefix = UC_TABLE_PREFIX;

	/**
	 * 数据库连接
	 * @var string
	 */
	protected $connection = UC_DB_DSN;

	/* 用户模型自动验证 */
	protected $_validate = array(
		/* 验证用户名 */
		//array('mobile', '11', -1, self::EXISTS_VALIDATE, 'length'), //用户名长度不合法
		//array('mobile', 'checkDenyMember', -2, self::EXISTS_VALIDATE, 'callback'), //用户名禁止注册
		//array('mobile', '', -3, self::EXISTS_VALIDATE, 'unique'), //用户名被占用
		//array('mobile', '/^1[3|4|5|7|8][0-9]\d{8}$/', -4, self::EXISTS_VALIDATE, 'regex'), //手机号码不正确

		/* 验证密码 */
		//array('password', '6,20', -1, self::EXISTS_VALIDATE, 'length'), //密码长度不合法

		/* 验证邮箱 */
		array('email', 'email', -2, self::EXISTS_VALIDATE), //邮箱格式不正确
		array('email', '1,32', -3, self::EXISTS_VALIDATE, 'length'), //邮箱长度不合法
		array('email', 'checkDenyEmail', -4, self::EXISTS_VALIDATE, 'callback'), //邮箱禁止注册
		array('email', '', -5, self::EXISTS_VALIDATE, 'unique'), //邮箱被占用

		/* 验证手机号码 */
		//array('mobile', '//', -9, self::EXISTS_VALIDATE), //手机格式不正确 TODO:
		//array('mobile', 'checkDenyMobile', -10, self::EXISTS_VALIDATE, 'callback'), //手机禁止注册
		//array('mobile', '', -11, self::EXISTS_VALIDATE, 'unique'), //手机号被占用
		);

	/* 用户模型自动完成 */
	protected $_auto = array(
		//array('password', 'think_ucenter_md5', self::MODEL_BOTH, 'function', UC_AUTH_KEY),
		array('regdate', NOW_TIME, self::MODEL_INSERT),
		array('regip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
		/*array('status', 'getStatus', self::MODEL_BOTH, 'callback'),*/
		);


	/**
	 * 根据配置指定用户状态
	 * @return integer 用户状态
	 */
	protected function getStatus(){
		return true; //TODO: 暂不限制，下一个版本完善
	}
	
	public function __construct(){
		parent::__construct();
		if($GLOBALS['Entrust'] == 'yes'){
			$this->_validate='';
		}
		
	}

	/**
	 * 注册一个新用户
	 * @param  string $username 用户名
	 * @param  string $password 用户密码
	 * @param  string $email    用户邮箱
	 * @param  string $mobile   用户手机号码
	 * @return integer          注册成功-用户信息，注册失败-错误编号
	 */
	public function register($firstName,$lastName,$email,$password,$signedUp){
		$data = array(
			'username' => $email,
			'email'    => $email,
			'salt' 	   => substr(uniqid(rand()), -6),
			'status'   => 0,
			);
		$data['password'] = md5(md5($password).$data['salt']);
		/* 添加用户 */
		if($this->create($data)){
			$uid = $this->add();
			$mid=0;
			if($uid)
			{
				list($nickName,$other)=explode('@',$email);
				//添加成功的话向member表中添加
				$Member = D("Home/Member");
				$mid=$Member->add_model($uid,$firstName,$lastName,$nickName);
				if($mid<=0)
				{
					$this->where("uid=$uid")->delete(); 
					return -10;
				}
				else
				{
					if($signedUp)
					{	
						//1=最新消息,2=优惠信息
						$dMemberFeed=D('Home/MemberFeed');
						$dMemberFeed->add_model($uid,1);
						$dMemberFeed->add_model($uid,2);
					}
					//$this->updateLogin($uid);
					//$dMember = D('Home/Member');
					//$Member->login($uid);
				}
			}
			return $uid>0 && $mid>0 ? $uid : 0; //0-未知错误，大于0-注册成功
		} else {
			return $this->getError(); //错误详情见自动验证注释
		}
	}

	/**
	 * 用户登录认证
	 * @param  string  $username 用户名
	 * @param  string  $password 用户密码
	 * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
	 * @return integer           登录成功-用户ID，登录失败-错误编号
	 */
	public function login($username, $password='', $type = 1){
		$map = array();
		$map['username'] = $username;
		$map['mobile'] = $username;
		$map['_logic'] = 'OR';
		/* 获取用户数据 */
		$users = $this->where($map)->order(" status desc")->select();
		if(is_array($users)){
			$user=$users[0];                               
			/* 验证用户密码 */
			if(md5($password) === $user['password']){
				$this->updateLogin($user['uid']); //更新用户登录信息
				return array(1,$user['uid']); //登录成功，返回用户ID
			} else {
				return array(-1,0); //密码错误
			}
		} 
	}
	
	public function adminlogin($username, $password, $type = 1){
		$map = array();
		$map['username'] = $username;
		$map['mobile'] = $username;
		$map['_logic'] = 'OR';
		/* 获取用户数据 */
		$user = $this->where($map)->find();
		if(is_array($user) && $user['status']){
			/* 验证用户密码 */
			if(md5($password) === $user['password']){
				$this->updateLogin($user['uid']); //更新用户登录信息
				return $user['uid']; //登录成功，返回用户ID
			} else {
				return -2; //密码错误
			}
		} else {
			return -1; //用户不存在或被禁用
		}
	}



	/**
	 * 获取用户信息
	 * @param  string  $uid         用户ID或用户名
	 * @param  boolean $is_username 是否使用用户名查询
	 * @return array                用户信息
	 */
	public function info($uid, $is_username = false){
		$map = array();
		if($is_username){ //通过用户名获取
			$map['username'] = $uid;
		} else {
			$map['uid'] = $uid;
		}

		$user = $this->where($map)->field('uid,username,email,mobile,status,password')->find();
		if(is_array($user) && $user['status'] = 1){
			return array($user['uid'], $user['username'], $user['email'], $user['mobile'], $user['password']);
		} else {
			return -1; //用户不存在或被禁用
		}
	}

	/**
	 * 检测用户信息
	 * @param  string  $field  用户名
	 * @param  integer $type   用户名类型 1-用户名，2-用户邮箱，3-用户电话
	 * @return integer         错误编号
	 */
	public function checkField($field, $type = 1){
		$data = array();
		switch ($type) {
			case 1:
				$data['username'] = $field;
				break;
			case 2:
				$data['email'] = $field;
				break;
			case 3:
				$data['mobile'] = $field;
				break;
			default:
				return 0; //参数错误
		}

		return $this->create($data) ? 1 : $this->getError();
	}

	/**
	 * 更新用户登录信息
	 * @param  integer $uid 用户ID
	 */
	protected function updateLogin($uid){
		$data = array(
			'uid'              => $uid,
			'updatetime' => NOW_TIME,
			);
		$this->save($data);
	}

	/**
	 * 更新用户信息
	 * @param int $uid 用户id
	 * @param string $password 密码，用来验证
	 * @param array $data 修改的字段数组
	 * @return true 修改成功，false 修改失败
	 * @author huajie <banhuajie@163.com>
	 */
	public function updateUserFields($uid, $password, $data){
		if(empty($uid) || empty($password) || empty($data)){
			$this->error = '参数错误！';
			return false;
		}

		//更新前检查用户密码
		if(!$this->verifyUser($uid, $password)){
			$this->error = '验证出错：密码不正确！';
			return false;
		}

		//更新用户信息
		$data = $this->create($data);
		if($data){
			return $this->where(array('uid'=>$uid))->save($data);
		}
		return false;
	}

	/**
	 * 验证用户密码
	 * @param int $uid 用户id
	 * @param string $password_in 密码
	 * @return true 验证成功，false 验证失败
	 * @author huajie <banhuajie@163.com>
	 */
	protected function verifyUser($uid, $password_in){
		$password = $this->getFieldById($uid, 'password');
		if(think_ucenter_md5($password_in, UC_AUTH_KEY) === $password){
			return true;
		}
		return false;
	}
	
	/**
	 * 验证此手机号码是否存在
	 * @param string $phone 手机号码
	 * @return true 验证成功，false 验证失败
	 */
	public function existsPhone($phone){
		$map['mobile'] = $phone;
		$user = $this->where($map)->field('uid,mobile')->find();
		if(is_array($user)){
			return true;
		} else {
			return false; 
		}
	}
	
	/**
	 * 验证此邮箱是否存在
	 * @param string $email 邮箱
	 * @return true 验证成功，false 验证失败
	 */
	public function existsEmail($email){
		$map['email'] = $email;
		$user = $this->where($map)->field('uid,email')->find();
		if(is_array($user)){
			return true;
		} else {
			return false; 
		}
	}
}
