<?php
/**
 * Create by wujianke
 * Date: 14-8-10
 * 功能: 和User表相关数据处理
 */
namespace Home\Model;
use Think\Model;

class UserModel extends Model{	
	/**
	* Created by wujianke
	* Date: 14-8-10
	* 功能:更新实体的手机号码字段
	* @param  $uid		  	用户ID  
	* @param  $mobile  	手机号码
	*/ 
	public function update_mobile($uid, $mobile)
	{
		$updatedata = array(
			'uid'              	=> $uid,
			'mobile' 			=> $mobile,
			);
		$this->save($updatedata);
	}
	public function update_email($uid, $email)
	{
		$updatedata = array(
			'uid'              	=> $uid,
			'email' 			=> $email,
			);
		$this->save($updatedata);
	}
	
	public function update_mobile_status($uid, $mobile)
	{
		$updatedata = array(
			'uid'              	=> $uid,
			'mobile' 			=> $mobile,
			'status'			=> 1,
			);
		$this->save($updatedata);
	}
	public function update_one($where,$data){
            return $this->where($where)->save($data);
        }
	public function get_User_By_Mobile($mobile)
	{
		return $this->where("mobile='{$mobile}'")->find();
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
	
	public function get_Verify_Mobile($mobile)
	{
		return $this->where("mobile='{$mobile}' and status=1")->find();
	}
	
	/**
	* Created by wujianke
	* Date: 14-8-10
	* 功能:根据手机号修改用户信息
	* @param  $username		  	用户名  
	* @param  $mobile  	手机号码
	* @param  $password   密码
	*/ 
	public function updateUserInfo($mobile,$username,$password){
     	$userinfo = $this->where("mobile={$mobile}")->find();
      	if(is_array($userinfo)){
        	$pwd = md5($password);
         	$data = array(
         	'username' => $username,
			'password' => $pwd,
			'updatetime' => NOW_TIME,
			'status'	=> 1,
			);
          	$userid = $this->where("uid={$userinfo[uid]}")->save($data);
      		if($userid){
           		//cookie('username',$username,array('expire'=>500*24*60*60,'prefix'=>''));
          		return 1;
          	}else{
             	return 0;
          	}
      	}            
	}
    
}
