<?php
/**
 * Created by wujianke
 * Date: 14-8-10
 * 功能: 和User表相关的业务逻辑处理
 */ 
namespace Home\Logic;
use Think\Model;

class UserLogic extends Model{
	
	public function add_model($moblie){
   		$UCData = array(
			'username' => $moblie,
			'email'    => $moblie,
			'mobile'   => $moblie,
			'createtime'  => NOW_TIME,
			'status'   => 0,                       
		);
		/* 添加用户 */
		if($this->create($UCData)){
			$uid = $this->add();
			if($uid)
			{
				return $uid;
			}
			else
			{	
				return array(-404,0);
			}
		}else {
			return array(-404,0);
		}	
	}

}