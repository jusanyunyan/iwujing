<?php
/**
 * Created by wujianke
 * Date: 14-8-10
 * 功能: 和UserVerifyToken表相关数据处理
 */
namespace Home\Model;
use Think\Model;

class UserVerifyTokenModel extends Model{

	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:获取Token实体
	 * @param  $vid  			验证ID  
	 * @param  $token_type  	Token类型  
	 * @return array  Token实体；-1 获取失败
	 */ 
	public function get_model($vid, $token_type = 0)
	{
		$maptoken = array();
		$maptoken['verify_id'] = $vid;
		$maptoken['token_type'] = $token_type;
		$token=$this->where($maptoken)->field('verify_id,token_type,token,expired_time')->find();
		
		if(is_array($token)){
			return $token;
		} else {
			return -1; 
		}
	}
	/**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:删除Token实体
	 * @param  $vid  			验证ID  
	 * @param  $token_type  	Token类型  
	 */ 
	public function del_model($vid, $token_type)
	{
		$deldata = array(
			'verify_id'         => $vid,
			'token_type' 		=> $token_type,
		);
		return $this->where($deldata)->delete(); 
	}
	 /**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:添加Token实体
	 * @param  $vid  			验证ID  
	 * @param  $token_type  	Token类型  
	 * @param  $code  			验证码  
	 * @param  $expired_time  	过期时间  
	 * @return int  大于0返回新增的实体ID，小于则添加失败
	 */ 
	public function add_model($vid, $token_type,$code,$expired_time)
	{
		
		$dataToken= array(
			'verify_id'            	=> $vid,
			'token_type' 			=> $token_type,
			'token'   				=> $code,
			'expired_time'   		=> $expired_time,
		);
		if($this->create($dataToken))
		{
			$vid=$this->add();			
			return $vid? $vid : 0; 			
		}
		else
		{
			return -1;
		}
	}
	
	 /**
	 * Created by wujianke
	 * Date: 14-8-10
	 * 功能:清理过期数据
	 */ 
	public function clear_overdue()
	{
		$map=array();
		$map['expired_time']  = array('lt',NOW_TIME);
		return $this->where($map)->delete(); 
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
}
