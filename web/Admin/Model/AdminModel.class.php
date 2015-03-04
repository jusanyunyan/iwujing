<?php
/**
 * Create by wujianke
 * Date: 2014-9-1
 * 功能： 和admin表相关的数据处理
 */

namespace Admin\Model;
use Think\Model;

class AdminModel extends Model{	
	public function adminlogin($username, $password){
		$map = array();
		$map['username'] = $username;
		/* 获取用户数据 */
		$user = $this->where($map)->find();
		if(is_array($user) && $user['status']){
			/* 验证用户密码 */
			if($password === $user['password']){
				$this->updateLogin($user['uid']); //更新用户登录信息
				return $user['uid']; //登录成功，返回用户ID
			} else {
				return -2; //密码错误
			}
		} else {
			return -1; //用户不存在或被禁用
		}
		exit;
	}
	
	protected function updateLogin($uid){
		$data = array(
			'uid'              => $uid,
			'update_time' => NOW_TIME,
			);
		$this->save($data);
	}
	
	/* 所有MODEL公共方法 */
	public function get_one($where,$field='')
	{
		return $this->field($field)->where($where)->find();
	}

	public function get_all($where,$field='')
	{
		return $this->field($field)->where($where)->select();
	}

	public function get_count($where,$field='')
	{
		return $this->field($field)->where($where)->count();
	}
	
	public function update_one($where,$data){
		return $this->where($where)->save($data);
 	}
}
