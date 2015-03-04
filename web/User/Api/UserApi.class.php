<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace User\Api;
use User\Api\Api;
use User\Model\UserModel;

class UserApi extends Api{
    /**
     * 构造方法，实例化操作模型
     */
    protected function _init(){  	
        $this->model = new UserModel();
    }


    /**
     * 用户登录认证
     * @param  string  $username 用户名
     * @param  string  $password 用户密码
     * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
     * @return integer           登录成功-用户ID，登录失败-错误编号
     */
    public function login($username, $password, $type = 3){
        return $this->model->login($username, $password, $type);
    }
    
 	public function adminlogin($username, $password, $type = 1){
        return $this->model->adminlogin($username, $password, $type);
    }
    
	/**
     * 获取用户信息
     * @param  string  $uid         用户ID或用户名
     * @param  boolean $is_username 是否使用用户名查询
     * @return array                用户信息
     */
    public function info($uid, $is_username = false){
        return $this->model->info($uid, $is_username);
    }
	
	/**
	 * Created by wujianke
 	 * Date: 14-8-10
	 * 验证此手机号码是否存在
	 * @param string $phone 手机号码
	 * @return true 验证成功，false 验证失败
	 */
	public function existsPhone($phone){
		return $this->model->existsPhone($phone);
	}
	/**
	 * Created by wujianke
 	 * Date: 14-8-10
	 * 验证此邮箱是否存在
	 * @param string $email 邮箱
	 * @return true 验证成功，false 验证失败
	 */
	public function existsEmail($email){
		return $this->model->existsEmail($email);
	}
	
	/**
	 * Created by wujianke
 	 * Date: 14-8-10
	 * 检查Email格式是否正确
	 * @param string $email 邮箱
	 * @return true 验证成功，false 验证失败
	 */
	public function checkemailformat($email) {
		return preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}
	/**
	 * Created by wujianke
 	 * Date: 14-8-10
	 * 检查Mobile格式是否正确
	 * @param string $mobile 手机号
	 * @return true 验证成功，false 验证失败
	 */
	public function checkmobileformat($mobile) {
		//return preg_match("/^1[3|4|5|7|8][0-9]\d{8}$/", $mobile);
		return preg_match("/^(((\+86|86)?1[3|4|5|7|8][0-9]\d{8})|((\+\d{2})|(\d{2}))\d{11})$/", $mobile);
	}
}
